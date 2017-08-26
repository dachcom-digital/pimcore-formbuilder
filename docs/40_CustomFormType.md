# Custom Form Type

Creating custom types with formbuilder is crazy simple. 
For example, you can create custom backend options without any javascript implementation.

Let's have a look how the choice field has been configured. 
Of course, you can adopt these settings for your custom form type - just set your class and your good to go.

```yaml
form_builder:
    types:
        # name your form type
        choice:
        
            # define the class
            class: Symfony\Component\Form\Extension\Core\Type\ChoiceType
            backend:
            
                # you either use one of those fields 
                # or your could create a custom type group
                #
                # - choice_fields
                # - datetime_fields
                # - other_fields
                # - hidden_fields
                # - buttons
                #
                form_type_group: choice_fields
                
                # add a label for the field
                label: 'form_builder_type.choice_type'
                
                # add your custom icon class
                icon_class: 'form_builder_icon_multi_select'
                
                # now define some fields
                fields:
                
                    # add expanded options since this an available option for the symfony choice type
                    options.expanded:
                    
                        # display_group_id defines in which display group extjs should render this field
                        # it's possible to create custom display groups or even new tabs for the backend configuration layout
                        display_group_id: attributes
                        
                        # there are several types available. readmore about the backend types below
                        type: checkbox
                        
                        #again, add a label for the backend field
                        label: 'form_builder_type_field.expanded'
                        
                        # no further configuration for a checkbox
                        config: ~
                        
                    # add multiple options since this an available option for the symfony choice type
                    options.multiple:
                        display_group_id: attributes
                        type: checkbox
                        label: 'form_builder_type_field.multiple'
                        config: ~
                        
                    # choices: this is a complex one
                    options.choices:
                        display_group_id: attributes
                        type: key_value_repeater
                        label: 'form_builder_type_field.choices'
                        
                        # we need a options transformer. it transforms backend values into a valid symfony choice values and back.
                        options_transformer: 'form_builder.options_transformer.choices'
                        config: ~
```


### Available Backend Types

Render different configuration types for ExtJs:

| Name | Description |
|------|-------|
| label | -- |
| tagfield | -- |
| numberfield | -- |
| textfield | -- |
| select | -- |
| key_value_repeater | -- |
| options_repeater | -- |