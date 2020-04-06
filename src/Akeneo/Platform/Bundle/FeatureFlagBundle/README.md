# FeatureFlagBundle

Simple, stupid and yet flexible [Feature Flag](https://www.martinfowler.com/articles/feature-toggles.html) system for the Symfony world.

TODO: evaluate and describe existing bundles

## Feature flags' configuration

Feature flags are defined by a _key_, representing the feature, and a _service_ which answers to the question "Is this feature enabled?". 

```yaml
// config/packages/akeneo_feature_flag.yml

akeneo_feature_flag:
    - onboarder: '@service_that_defines_if_onboarder_feature_is_enabled'
    - foo: '@service_that_defines_if_foo_feature_is_enabled'
    - ...
```

The most important here is to decouple the decision point (the place where I need to know if a feature is enabled) from the decision logic (how do I know if this feature is enabled). 

Your feature flag service must respect the following contract:

```php
namespace Akeneo\Platform\Bundle\FeatureFlagBundle;

interface FeatureFlag
{
    public function isEnabled(): bool
}    
```

Your feature flag service must be tagged with `akeneo_feature_flag`.

### Examples

Let's take a very simple example: we say we want to (de)activate the _Onboarder_ feature via an environment variable. All we have to do is to declare the following service:

```yaml
services:
    service_that_defines_if_onboarder_feature_is_enabled:
        class: 'Akeneo\Platform\Bundle\FeatureFlagBundle\Configuration\EnvVarFeatureFlag'
        arguments:
            - '%env(FLAG_ONBOARDER_ENABLED)%'
        tags: ['akeneo_feature_flag']
```

Behind the scenes, the very simple `EnvVarFeatureFlag` class is called:

```php
namespace Akeneo\Platform\Bundle\FeatureFlagBundle;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

class EnvVarFeatureFlag implements FeatureFlag
{
    private $isEnabled;

    public function __construct(bool $isEnabled)
    {
        $this->isEnabled = $isEnabled;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }
}
``` 

Another example. Imagine now you want to allow Akeneo people working at Nantes to access a beta `foo` feature. All you have to do is declare in your code a service that implements `Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag`.

```yaml
services:
    service_that_defines_if_foo_feature_is_enabled:
        class: 'Akeneo\My\Own\Namespace\FooFeatureFlag'
        arguments:
            - '@request_stack'
        tags: ['akeneo_feature_flag']
``` 

```php
namespace Akeneo\My\Own\Namespace;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

class FooFeatureFlag implements FeatureFlag
{
    private $akeneoIpAddress = //...
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }
    
    public function isEnabled(): bool
    {
        return $this->requestStack->getCurrentRequest() === $this->$akeneoIpAddress; 
    }
}

```

### Provided feature flag classes

To ease developments, the _FeatureFlagBundle_ comes with a few ready to use implementations. When you want to use those classes, all you have to do is to declare a service.  

- `Akeneo\Platform\Bundle\FeatureFlagBundle\EnvVarFeatureFlag`: know if a feature is activated by checking an environment variable.  
- ...

## Using feature flag in your code

```php
    $flags = $container->get('feature_flags_registry');
    if ($flags->isEnabled('myFeature')) { //...
```

### Short living feature flags

inject the feature flag service registry in your code

simple if/else is OK

### Long living feature flags

avoid crippling code with if/else

use inversion of control and service factories or strategy pattern instead
