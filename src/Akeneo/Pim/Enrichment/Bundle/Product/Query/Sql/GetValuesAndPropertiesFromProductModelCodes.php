<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class GetValuesAndPropertiesFromProductModelCodes
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fetchByProductModelCodes(array $productModelCodes): array
    {
        $query = <<<SQL
WITH
    product_model AS (
        SELECT
            product_model.id,
            product_model.code,
            product_model.created,
            root_product_model.code AS parent_code,
            GREATEST(product_model.updated, COALESCE(root_product_model.updated, 0)) as updated,
            JSON_MERGE_PRESERVE(COALESCE(root_product_model.raw_values, '{}'), COALESCE(product_model.raw_values, '{}')) AS raw_values,
            family.code AS family_code,
            family_variant.code AS family_variant_code,
            product_model.parent_id,
            family_variant.family_id,
            family_variant.id AS family_variant_id,
            attribute.code AS attribute_as_label_code
        FROM
            pim_catalog_product_model product_model
            INNER JOIN pim_catalog_family_variant family_variant ON family_variant.id = product_model.family_variant_id
            INNER JOIN pim_catalog_family family ON family.id = family_variant.family_id
            INNER JOIN pim_catalog_attribute attribute ON family.label_attribute_id = attribute.id 
            LEFT JOIN pim_catalog_product_model root_product_model ON product_model.parent_id = root_product_model.id
        WHERE
            product_model.code IN (:productModelCodes)
    ),
    product_model_categories AS (
        SELECT
            product_model.id as product_model_id,
            JSON_ARRAYAGG(category.code) AS category_codes
        FROM
            product_model
            INNER JOIN pim_catalog_category_product_model category_product_model ON category_product_model.product_model_id = product_model.id
            INNER JOIN pim_catalog_category category ON category.id = category_product_model.category_id
        GROUP BY product_model.id
    ),
    root_product_model_categories AS (
        SELECT
            product_model.id AS product_model_id,
            JSON_ARRAYAGG(category.code) AS category_codes
        FROM
            product_model
            INNER JOIN pim_catalog_category_product_model category_product_model ON category_product_model.product_model_id = product_model.parent_id
            INNER JOIN pim_catalog_category category ON category.id = category_product_model.category_id
        GROUP BY product_model.id
    ),
    product_model_family_labels AS (
        SELECT 
            family.family_id,
            JSON_OBJECTAGG(locale.code, family_translation.label) AS labels
        FROM 
            (SELECT DISTINCT product_model.family_id FROM product_model) family  
            CROSS JOIN pim_catalog_locale locale
            LEFT JOIN pim_catalog_family_translation family_translation ON family_translation.foreign_key = family.family_id AND family_translation.locale = locale.code
        WHERE locale.is_activated = true
        GROUP BY family.family_id
    )
    SELECT
        product_model.*,
        JSON_MERGE_PRESERVE(
            COALESCE(product_model_categories.category_codes, JSON_ARRAY()),
            COALESCE(root_product_model_categories.category_codes, JSON_ARRAY())
        ) AS category_codes,
        COALESCE(root_product_model_categories.category_codes, JSON_ARRAY()) as ancestor_category_codes,
        COALESCE(product_model_family_labels.labels, JSON_ARRAY()) AS family_labels
    FROM product_model
    LEFT JOIN product_model_categories ON product_model_categories.product_model_id = product_model.id
    LEFT JOIN root_product_model_categories ON root_product_model_categories.product_model_id = product_model.id
    LEFT JOIN product_model_family_labels ON product_model_family_labels.family_id = product_model.family_id
SQL;

        $rows = $this->connection->fetchAll(
            $query,
            ['productModelCodes' => $productModelCodes],
            ['productModelCodes' => Connection::PARAM_STR_ARRAY]
        );

        $platform = $this->connection->getDatabasePlatform();
        $results = [];
        foreach ($rows as $row) {
            $values = json_decode($row['raw_values'], true);

            $results[$row['code']] = [
                'id' => (int) $row['id'],
                'code' => $row['code'],
                'created' => Type::getType(Type::DATETIME_IMMUTABLE)->convertToPHPValue($row['created'], $platform),
                'updated' => Type::getType(Type::DATETIME_IMMUTABLE)->convertToPHPValue($row['updated'], $platform),
                'family_code' => $row['family_code'],
                'family_labels' => json_decode($row['family_labels'], true),
                'family_variant_code' => $row['family_variant_code'],
                'category_codes' => json_decode($row['category_codes']),
                'ancestor_category_codes' => json_decode($row['ancestor_category_codes']),
                'parent_code' => $row['parent_code'],
                'values' => $values,
                'parent_id' => (int) $row['parent_id'],
                'labels' => isset($values[$row['attribute_as_label_code']]) ? $values[$row['attribute_as_label_code']] : [],
            ];
        }

        return $results;
    }
}