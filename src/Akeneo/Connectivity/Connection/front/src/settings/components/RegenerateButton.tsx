import React, {FC, useContext} from 'react';
import {IconButton} from '../../common';
import {UpdateIcon} from '../../common/icons';
import {TranslateContext} from '../../shared/translate';

interface Props {
    onClick: () => void;
    id?: string;
}

export const RegenerateButton: FC<Props> = ({onClick, id = undefined}: Props) => {
    const translate = useContext(TranslateContext);

    return (
        <IconButton
            data-testid={id}
            onClick={onClick}
            title={translate('akeneo_connectivity.connection.edit_connection.credentials.action.regenerate')}
        >
            <UpdateIcon />
        </IconButton>
    );
};
