# FeatureFlagBundle

Simple, stupid and yet flexible Feature Flags system for the Symfony world. Please, take 30 minutes to read the tremendous article [Feature Toggles (aka Feature Flags)
](https://www.martinfowler.com/articles/feature-toggles.html). Feature flags are not an easy topic. They present great power but come with many burdens.  

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

### About the frontend

Flags are of course also available for frontend. Behind the scenes, a backend route (TODO: ROUTE HERE) is called. It returns a JSON response answering if the feature is enabled or not. See the part _Knowing if a feature is enabled_ for more information.


## Using feature flags in your code

### Knowing if a feature is enabled

#### Backend

A service called `akeneo_feature_flags` exists to determine if the feature you have configured in `config/packages/akeneo_feature_flag.yml` is enabled or not. This is the one and only backend entry point you have to use.

```php
$flags = $container->get('akeneo_feature_flags');
if ($flags->isEnabled('myFeature')) { //...
```

#### Frontend

TODO with Paul: the idea is to have a simple service `AkeneoFeatureFlags`. Maybe it will be embedded in a fetcher to act as some sort of cache.

### Short living feature flags

**Flags that will live from a few days to a few weeks.**

This happens typically when you develop a small feature bits by bits. At present, the feature is not ready to be presented to the end user, but with a few more pull requests and tests, this will be the case. 

For those use cases, we'll go simple. Simply inject the feature flags service (backend or frontend) in your code and branch with a simple `if/else`. 

**This way of working works only and only if you clean all those hideous conditional when your feature is ready to use.** Otherwise, the code will quickly become hell of a maze with all flags setup by all different teams. 

**Also, please take extract care on the impact your flag could have on other teams' flags.** If it becomes tedious, please adopt the same strategy than for long living flags instead.

### Long living feature flags

**Flags that will live more than a few weeks.**

The 

avoid crippling code with if/else

use inversion of control and service factories or strategy pattern instead


## Part about what we can do when flagging?

TODO: for instance:
- 403 forbidden routes
- ...
