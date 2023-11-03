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