# Form & Field Attributes
![](http://g.recordit.co/mExDy4lMIe.gif)
It's possible to add specific attributes to each form and and each underlying field.

## Change Attribute Selection
By default, attributes are shipped as symfony parameters so it's easy to override them:

```yml
parameters:
    form_builder_form_attributes:
        - ['my-data-attribute','My Data Attribute']
    form_builder_field_attributes: '%form_builder_form_attributes%'
```