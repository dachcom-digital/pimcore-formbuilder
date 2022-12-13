## Funnel: Funnel Actions

There are two types of general funnel actions. 

### Dynamic Actions
![image](https://user-images.githubusercontent.com/700119/207158937-39e05e12-473f-4c5c-a69b-ec2d8e333c3a.png)
If a funnel channel is configured as `dynamicFunnelActionAware`, an admin is able to add 1:n action definitions.
Additionally, template placeholder will be available.

### Preconfigured Actions
![image](https://user-images.githubusercontent.com/700119/207161010-0077440a-317d-4035-9acf-fc2ffbbd3b83.png)
If a funnel channel has preconfigured action definitions by returning an array via `getFunnelActionDefinitions`,
an admin is able to set actions to these exact predefined buttons.

### Global Configuration
- `Allow Invalid Form Submission`: This allows you to define an action, which skips any form constraints

***

### Funnel Action Types
There some predefined funnel action types:

#### Type: Channel Route Action
![image](https://user-images.githubusercontent.com/700119/207159146-4f065da1-dff1-4c38-a515-026969611f54.png)
This action navigates to a channel.

- `Allow Invalid Form Submission`: This allows you to define an action, which skips any form constraints.

#### Type: Form Route Action
![image](https://user-images.githubusercontent.com/700119/207159387-48d82e4a-c21b-44ac-bcfb-a9f351426975.png)
This action navigates back to the form.

- `Populate Form`: Restores form values

#### Type: Disable Action
![image](https://user-images.githubusercontent.com/700119/207159504-8bb6f92e-1e0f-420c-b43d-af0793465e7d.png)

This action skips any further process (mostly used if a given layer comes with some predefined action definitions)
No further configuration needed. This action won't show up in form rendering process.