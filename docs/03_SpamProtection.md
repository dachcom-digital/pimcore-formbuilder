# Spam Protection

## Double-Opt-In
Read more about the double-opt-in feature [here](./04_DoubleOptIn.md).

## HoneyPot
The Honeypot Field is enabled by default. You can disable it via [configuration flags](100_ConfigurationFlags.md).

```yaml
form_builder:
    spam_protection:
        honeypot:
            field_name: 'inputUserName'     # this is the default value
            enable_inline_style: true       # this is the default value
            enable_role_attribute: true     # this is the default value and will add the role="presentation" attribute
```

***

## reCAPTCHA v3
reCAPTCHA v3 returns a score for each request without user friction. 
The score is based on interactions with your site and enables you to take an appropriate action for your site. 
Register reCAPTCHA v3 keys [here](https://g.co/recaptcha/v3).

> **Important!** It is recommended to load the recaptcha api on every page request.
> To hide the badge on documents without forms, read our [frontend tips](./90_FrontendTips.md)

1. Get your keys at https://g.co/recaptcha/v3
2. Add site and secret key to your formbuilder settings:

```yaml
form_builder:
    spam_protection:
        recaptcha_v3:
            secret_key: 'YOUR_SECRET_KEY'
            site_key: 'YOUR_SITE_KEY'
```

3. Add the reCAPTCHA field to your form
4. Enable the reCAPTCHA [javascript module](./91_Javascript.md)
4. Done

## Friendly Captcha
Friendly Captcha is a system for preventing spam on your website.
You can add the Friendly Captcha widget to your form to fight spam, with little impact to the user experience.

1. Set your application: https://docs.friendlycaptcha.com/#/installation?id=_1-generating-a-sitekey
2. Add site and secret key to your formbuilder settings:

```yaml
form_builder:
    spam_protection:
        friendly_captcha:
            secret_key: 'YOUR_SECRET_KEY'
            site_key: 'YOUR_SITE_KEY'
            eu_only: false # see https://docs.friendlycaptcha.com/#/eu_endpoint (enterprise only)
```

3. Add the "Friendly Captcha" field to your form
4. Enable the FriendlyCaptcha [javascript module](./91_Javascript.md)
4. Done

***

## Cloudflare Turnstile
Turnstile delivers frustration-free, CAPTCHA-free web experiences to website visitors - with just a simple snippet of free code.
Moreover, Turnstile stops abuse and confirms visitors are real without the data privacy concerns or awful user experience of CAPTCHAs.

1. Set your application: https://dash.cloudflare.com/
2. Add site and secret key to your formbuilder settings:

```yaml
form_builder:
    spam_protection:
        cloudflare_turnstile:
            secret_key: 'YOUR_SECRET_KEY'
            site_key: 'YOUR_SITE_KEY'

```

3. Add the "Cloudflare Turnstile" field to your form
4. Enable the CloudFlareTurnstile [javascript module](./91_Javascript.md)
4. Done

***

## Math Captcha
This simple math captcha form provides a lightweight and session-free way to prevent automated submissions.
It offers three difficulty levels, allowing customization based on security needs.
The captcha generates random math problems that users must solve before submitting the form.
Since no session storage is required, it can be easily integrated with minimal overhead.

> [!CAUTION]  
> The Math captcha doesn't solve the problem of figuring out that a user has previously solved the captcha.
> It only protects individual requests and might help if you're in the middle of a spam attack.
> However, it will give you time, to set up stronger spam protection like 
> recaptcha, turnstile or friendly captcha (which are all supported by this bundle) to get rid of smart bots!

### Encryption Secret

> [!IMPORTANT]  
> Math Captcha requires a valid encryption key!
> It uses the `%pimcore.encryption.secret%` as default, but you're able to set a dedicated one:

```yaml
form_builder:
    spam_protection:
        math_captcha:
            encryption_secret: 'my-very-long-encryption-secret'
```

### Math Captcha TTL | Expiration
To prevent replay attacks in a long-term view, a math captcha gets invalided after 30 minutes.

If you want to change the value, you need to update the configuration

```yaml
form_builder:
    spam_protection:
        math_captcha:
            hash_ttl: 30 # 30 minutes (default value)
```

***

## Email Checker
The Email Checker Validator is available, if you've added at least one service. Per default, no service is registered by default. 

This validator includes all services tagged with `form_builder.validator.email_checker`.
If one of those services returns false in `isValid()` method, the validator will fail.

### [BUILT IN] Disposable Email Domain Checker
If enabled, this checker will fetch every 24h a database (stored in `%kernel.project_dir%/var/tmp/form-builder-email-checker` via flysystem) with known disposable mail hosts from [disposable/disposable](https://github.com/disposable/disposable).
After that, the validator will check the given domain of an email address against the database.

This service is not available per default and needs to be enabled if you want to use it:

```yaml
form_builder:
    spam_protection:
        email_checker:
            disposable_email_domains:
                enabled: true
                include_subdomains: false # Also search host as subdomain. Default: false. Note, that this can be a huge performance impact
```

### Create custom Email Checker

```yaml
services:
    App\Validator\EmailChecker\MyEmailChecker:
        tags:
            - { name: form_builder.validator.email_checker }
```

```php
<?php

namespace App\Validator\EmailChecker;

use FormBuilderBundle\Configuration\Configuration;
use League\Flysystem\FilesystemOperator;
use function Symfony\Component\String\u;

final class MyEmailChecker implements EmailCheckerInterface
{
    public function isValid(string $email, array $context): bool
    {
        // do your validation here
        return true;
    }
}
```