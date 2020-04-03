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

The most important here is to decouple the decision point (the place where I need to know if a feature is enabled) from the decision logic (how do I know this feature is enabled). 

Your feature flag must respect the following contract:

```php
interface FeatureFlag
{
    public function isEnabled(): bool
}    
```

### Examples

Let's take a very simple example. Let's say we want to (de)activate the Onboarder feature via an environment variable. All we have to do is to declare the following service:

```yaml
services:
    service_that_defines_if_onboarder_feature_is_enabled:
        class: 'Akeneo\FeatureFlagBundle\Configuration\EnvVarFeatureFlag'
        arguments:
            - '%env(FLAG_ONBOARDER_ENABLED)%'
```

Behind the scenes, the very simple `EnvVarFeatureFlag` is called:

```php
use TODO\FeatureFlag;

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

Another example. Imagine now you want to allow Akeneo people working at Nantes to access a beta `foo` feature. All you have to do is declare in your code a service that implements `TODO\FeatureFlag`.

```yaml
services:
    service_that_defines_if_foo_feature_is_enabled:
        class: 'Akeneo\My\Own\Path\FooFeatureFlag'
        arguments:
            - '@request_stack'
``` 

```php
use TODO\FeatureFlag;

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

- EnvVarFeatureFlag
- ...

## Using feature flag in your code

### Short living feature flags

inject your feature flag service declared previously

simple if/else is OK

### Long living feature flags

avoid crippling code with if/else

use inversion of control and service factories or strategy pattern instead
