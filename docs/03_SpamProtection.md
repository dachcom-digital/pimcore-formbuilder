# Spam Protection

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