## Funnels | Root Form
Some useful hints, if you're going wild with the FormBuilder funnel feature.

### Serialization
During the whole funnel workflow process, data gets serialized all the time.
While doing this, we're doing this within the group `OutputWorkflow`.

If you've extended root forms with some complex form types, 
make sure your serializer data is configured within the right group:

```yaml
# Resources/config/serialization/MyEntity.yaml
App\Entity\MyEntity:
    attributes:
        id:
            groups: [Default, OutputWorkflow]

```