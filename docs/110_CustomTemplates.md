# Custom form templates
You can use your own custom form templates.

***

## Configuration

If you don't have a configuration file yet, create one `config/packages/form_builder.yaml`

```yaml
form_builder:
  form:
    templates:
      my_custom_layout:
        value: 'my_custom_layout.html.twig'
        label: 'form_builder_form_template.my_custom_layout'
        default: false
```

Then you need to create your own templates in the `templates/bundles/FormBuilderBundle/form/theme/` folder

`templates/bundles/FormBuilderBundle/form/theme/macro/my_custom_layout.html.twig`

```html
{% macro form_builder_form_head() %}
    <div class="row">
{% endmacro %}

{% macro form_builder_form_foot() %}
    </div>
{% endmacro %}

{% macro form_builder_form_message(flash_messages) %}
    {% if flash_messages is not empty %}
        <div class="col-12">
            {% for label, messages in flash_messages %}
                {% for message in messages %}
                    <div class="alert alert-{{ label == 'error' ? 'danger' : 'success' }}">
                        {{ message|raw }}
                    </div>
                {% endfor %}
            {% endfor %}
        </div>
    {% endif %}
{% endmacro %}
```

`templates/bundles/FormBuilderBundle/form/theme/my_custom_layout.html.twig`

```html
{% extends 'bootstrap_5_layout.html.twig' %}

{% use '@FormBuilder/form/theme/type/dynamic_multi_file.html.twig' %}
{% use '@FormBuilder/form/theme/type/html_tag.html.twig' %}
{% use '@FormBuilder/form/theme/type/instructions.html.twig' %}
{% use '@FormBuilder/form/theme/type/snippet.html.twig' %}
{% use '@FormBuilder/form/theme/type/container.html.twig' %}
{% use '@FormBuilder/form/theme/type/friendly_captcha.html.twig' %}
{% use '@FormBuilder/form/theme/type/cloudflare_turnstile.html.twig' %}

{%- block form_widget_compound -%}
    {%- if form is not rootform -%}
        <div {{ block('widget_container_attributes') }}>
    {% endif %}
    {%- if form is rootform -%}
        {{ form_errors(form) }}
    {%- endif -%}
    {{ block('form_rows') }}
    {{ form_rest(form) }}
    {%- if form is not rootform -%}
        </div>
    {% endif %}
{%- endblock form_widget_compound -%}

{% block checkbox_radio_label %}
    {# [...] #}
{% endblock checkbox_radio_label %}

{% block help_text_label %}
    {# [...] #}
{% endblock help_text_label %}

{% block form_widget_simple -%}
    {# [...] #}
{%- endblock form_widget_simple %}

{% block textarea_widget -%}
    {# [...] #}
{%- endblock textarea_widget %}

{% block choice_widget -%}
    {# [...] #}
{%- endblock choice_widget %}

{% block checkbox_widget -%}
    {# [...] #}
{%- endblock checkbox_widget %}

{% block form_builder_honeypot_row -%}
    {# [...] #}
{%- endblock form_builder_honeypot_row %}

{% block form_row -%}
    <div class="formbuilder-row {{ attr['data-template'] is defined ? attr['data-template'] : 'col-12' }}">
        {{ parent() }}
    </div>
{%- endblock form_row %}

{% block button_row -%}
    <div class="formbuilder-row {{ attr['data-template'] is defined ? attr['data-template'] : 'col-12' }}">
        {{ parent() }}
    </div>
{%- endblock button_row %}

{% block checkbox_row -%}
    <div class="formbuilder-row {{ attr['data-template'] is defined ? attr['data-template'] : 'col-12' }}">
        {{ form_widget(form) }}
        {{ form_errors(form) }}
    </div>
{%- endblock checkbox_row %}

{% block radio_row -%}
    <div class="formbuilder-row {{ attr['data-template'] is defined ? attr['data-template'] : 'col-12' }}">
        {{ form_widget(form) }}
        {{ form_errors(form) }}
    </div>
{%- endblock radio_row %}

{% block form_builder_friendly_captcha_type_row %}
    <div class="formbuilder-row {{ attr['data-template'] is defined ? attr['data-template'] : 'col-12' }}">
        <div class="form-group">
            {{ form_widget(form) }}
            {{ form_errors(form) }}
        </div>
    </div>
{% endblock form_builder_friendly_captcha_type_row %}

{% block form_builder_cloudflare_turnstile_type_row %}
    <div class="formbuilder-row {{ attr['data-template'] is defined ? attr['data-template'] : 'col-12' }}">
        <div class="form-group">
            {{ form_widget(form) }}
            {{ form_errors(form) }}
        </div>
    </div>
{% endblock form_builder_cloudflare_turnstile_type_row %}

{% block form_builder_container_collection_widget %}
    {%- set attr = attr|merge({class: (attr.class|default('') ~ ' row')|trim}) -%}
    {{ parent() }}
{% endblock form_builder_container_collection_widget %}
```

! Do not forget to add translations of `form_builder_form_template.my_custom_layout`

