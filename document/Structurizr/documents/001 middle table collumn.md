## Adding columns to middle tables in many-to-many relationships

- Thêm cột vào bảng trung gian trong quan hệ many-to-many
- hiện giá trị cột này trên UI
- thực hiện tuto: https://forum.espocrm.com/forum/developer-help/developer-tutorials/98592-adding-columns-to-middle-tables-in-many-to-many-relationships

## Sum up
- in ` entityDefs.json/ENTITY.json links FIELDNAME ` has config to add custom collumn. Ex:
```json
{
    ...
    "links": {​
        ...
        "contacts": {
            ...
            "additionalColumns": {
              "role": {
                  "type": "varchar",
                  "len": 40
              }
            },
        }
    }
}​

```
    
## Content:
- This tutorial explains how to:

Add a middle column (in Espo language, it is called a mid key) to a many-to-many relationship.

Add the mid key to the UI to allow it to be seen and modified by users.

Update the mid key using the query builder via PHP. The situation: I want to relate Contacts (existing entity) to Vendors (custom entity). The Contact should have a Role which is different for each Vendor. Note: This is the same situation as what exists between Contacts and Accounts.

- An example of the requirement is as follows:

Michael Scott is a Contact.

Dunder Mifflin and Michael Scott Paper Company are Vendors.

At Dunder Mifflin, Michael Scott is the Regional Manager. At Michael Scott Paper Company, Michael Scott is the President. In each Vendor detail view, I want to see Michael Scott listed with the correct title.

### Part 1: Creating the relationship and adding a column to the middle table

Create the custom Vendor entity using the normal tools and methods.

Add a many-to-many relationship between Contact and Vendor.

- The important settings are as follows:

Relationship names: "vendors" and "contacts"

Middle table name: "contactVendor"

Labels: These are not important, but use "Vendors" and "Contacts" to make things easier, at least at first.

IMPORTANT: Add a Link Multiple Field on the right side for Contacts. The additional column in the middle table can also be used in list views, but this tutorial requires a Link Multiple Field.

Add the following code to ` custom/Espo/Modules/MyModule/Resources/metadata/entityDefs/Vendor.json `:​
```json
{
    ...
    "links": {​
        ...
        "contacts": {
            ...
            "additionalColumns": {
              "role": {
                  "type": "varchar",
                  "len": 40
              }
            },
        }
    }
}​

```

### Part 2 - Showing the additional field in the UI

In summary, the relationship and additional column in the middle table have been established. What follows is the process for showing the value of additional column to the user. To accomplish that, each entity (Contact and Vendor) must be modified. Some of the modifications involve telling Espo to use non-storable fields. In other words, the fields are populated by relationships instead of directly from the database, which is the more typical method.

The next steps involve modifying the Vendor entity, which happen in ` custom/Espo/Modules/MyModule/Resources/metadata/entityDefs/Vendor.json `:

Add a new field called contactRole to store the value in the role field of the middle table. The new field is not storable, which means it is indirectly populated by Espo instead of directly through a call to the database. You can name this field whatever you want as long as it is unique in terms of the fields for the Vendor entity.​​

Add a property called columnAttributeMap to the link for the Contact relationship. It must contain a key-value pair.

The key of the property, role, must match the name of the new field in the middle table.

The value of the property, contactRole, must match the name of the new field of the Vendor entity.

Add several properties to the contacts field and modify some of the existing properties. I don't know what several of the properties do, so I won't try to explain. Copy, paste, and modify to fit your situation. Note: The value of the role property is vendorRole; this value must match the name of the non-storable field in the Contact entity.

For now, we are using a default view called views/fields/link-multiple-with-role, which is in client/src/fields/views/link-multiple-with-columns.js.

```json
{
    ...
    "fields": {​
        ...
        "contacts": {
            "type": "linkMultiple",
            "layoutDetailDisabled": false,
            "layoutMassUpdateDisabled": false,
            "layoutListDisabled": false,
            "noLoad": false,
            "importDisabled": false,
            "exportDisabled": false,
            "customizationDisabled": false,
            "columns": {
                "role": "vendorRole"
            },
            "additionalAttributeList": [
                "columns"
            ],
            "view": "views/fields/link-multiple-with-columns",
            "default": "javascript: return {contactsIds: []}",
            "isCustom": true
        },​
        "contactRole": {
            "type": "varchar",
            "notStorable": true,
            "utility": true,
        }
    },
    "links": {​
        ...
        "contacts": {
            ...
            "columnAttributeMap": {
              "role": "contactRole"
            }​
        }
    }
}​
```

- A similar list of steps must be implemented for the Contact entity in ` custom/Espo/Custom/Resources/metadata/entityDefs/Contact.json `:

Add a new field to store the text in the UI and an additional property to the link for the Vendor relationship.

The new property for the link is called columnAttributeMap. It must contain a key-value pair.

The key of the property, role, must match the name of the field in the middle table.

The value of the property, vendorRole, must match the name of the new field of the Vendor entity (see below).

The new field, vendorRole, is not storable, which means it is indirectly populated by Espo instead of directly through a call to the database. You can name this field whatever you want as long as it is unique in terms of the fields for the Contact entity.

```json
{
    ...
    "fields": {​
        ...
        "vendorRole": {
            "type": "varchar",
            "notStorable": true,
            "utility": true
        }
    },
    "links": {​
        ...
        "contacts": {
            ...
            "columnAttributeMap": {
              "role": "vendorRole"
            }​
        }
    }
}​
```


- Stopping Point

If you need role to be a free - from text field, you can stop here.

​​​​​​If you need to create a list of options for the role field continue with this tutorial.​

The role field is now visible, editable, and functional in the Vendor detail view as a free - from textbox.

Users can enter any value in the role field and save the record, which will insert the value into the middle table.

- Remember to set the other files as necessary:

    Layouts - Detail, List, Search, and other views

Languages and labels

It is possible to create a bottom panel that shows the value of the role field.You would need to use the vendorRole field for the view of the contact record.

### Part 3: Change the role field to be an enum instead of a varchar to give the user a list of options

Changing role from varchar to enum allows you to present the user with a list of options. You can accomplish this in the following ways:

Store a list of generic options that every Contact to Vendor relationship will have to use for the value of role

The list of options could be Pizza, Cheese, and Paper.

Every Contact that relates to a Vendor will have to choose between Pizza, Cheese, and Paper as the value of role.

Store a list of custom options in each Vendor record, which allows the user to set a value for role that is stored specifically in the related Vendor record.

The list of options is stored in the Vendor record. Vendor A could store Desk, Chair, and Table as the options while Vendor B could store Grass, Tree, and Flower as the options.

Contacts related to Vendor A would be able to choose from Desk, Chair, and Table as the value of role. 
Contacts related to Vendor B would be able to choose from Grass, Tree, and Flower as the value of role.
This method is what you see when you relate a User to a Team.

I will update this tutorial in the future to explain the steps involved with each process. The newer view called link-multiple-with-columns implements enums in a different way than the older view called link-multiple-with-role. I do not yet know how to use the newer view to accomplish both methods. For now, you can see an example of each method by searching for the aforementioned views in client/src/.​

### Update and find in backend

- To update the value of role using the ORM, there are multiple methods.

1. Method 1 - relate()

```php
$vendor = $this->em->getRDBRepository(Vendor::ENTITY_TYPE)->where(...)->findOne();
$contact = $this->em->getRDBRepository(Contact::ENTITY_TYPE)->where(...)->findOne();

$this->em->getRDBRepository(Vendor::ENTITY_TYPE)->getRelation($vendor, 'contacts')->relate($contact, array ('role' => "New Role Here"));​

```

2. Method 2 - queryBuilder()

```php
$vendor = $this->em->getRDBRepository(Vendor::ENTITY_TYPE)->where(...)->findOne();
$contact = $this->em->getRDBRepository(Contact::ENTITY_TYPE)->where(...)->findOne();​

$updateQuery = $this->em->getQueryBuilder()->update()->in('ContactVendor')
->set(array('role' => "New Role Here"))
->where(array(
  'contactId' => $contact->getId(),
  'vendorId' => $vendor->getId(),
))
->build();

$this->em->getQueryExecutor()->execute($updateQuery);​ 

```

- To find records based on the value of role using the ORM, there are multiple methods.​

For example, this is a filter in ` custom/Espo/Modules/MyModule/Classes/Select/Vendor/BoolFilters/OnlyOwners.php ` that finds Vendors with Contacts who have a role of Owner.

```php
namespace Espo\Modules\MyModule\Classes\Select\Vendor\BoolFilters;

use Espo\Core\Select\Bool\Filter;

use Espo\ORM\Query\{
  SelectBuilder,
  Part\Where\OrGroupBuilder,
  Part\WhereClause,
};

class OnlyOwners implements Filter
{
  public function __construct() {
  }

  public function apply(SelectBuilder $queryBuilder, OrGroupBuilder $orGroupBuilder): void
  {
    $queryBuilder
      ->leftJoin('contactVendor', 'cv', ['cv.vendorId:' => 'vendor.id'])
      ->leftJoin('contact', 'c', ['cv.contactId:' => 'c.id']);

    $orGroupBuilder->add(
      WhereClause::fromRaw(array(
        'cv.role' => "Owner",
      ))
    );
  }
}​
```

Here is a query builder statement on its own:

```php

$selectQuery = $this->em->getQueryBuilder()
  ->select(['id'])
  ->from(Vendor::ENTITY_TYPE)
  ->leftJoin('contactVendor', 'cv', ['cv.vendorId:' => 'vendor.id', 'cv.deleted' => false, ])
  ->leftJoin('contact', 'c', ['cv.contactId:' => 'c.id', 'c.deleted' => false, ])
  ->where(['cv.role' => "Owner"])
  ->build();

$pdoStatement = $this->em->getQueryExecutor()->execute($selectQuery);
$rows = $pdoStatement->fetchAll(\PDO::FETCH_ASSOC);​

```

### Part 4: Add custom list views in related entities that show the role
- read in the tuto link
