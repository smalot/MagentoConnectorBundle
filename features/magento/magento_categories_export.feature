@javascript
Feature: Magento category export
  In order to view categories in Magento
  As an Administrator
  I need to be able to export categories to Magento

  Scenario: Successfully export attributes to Magento
    Given a "magento" catalog configuration
    And the following category:
      | code         | label-en_US      | label-fr_FR                 | parent    |
      | computers    | Computers        | Ordinateurs                 | default   |
      | desktops     | Desktops         | Ordinateurs de bureau       | computers |
      | laptops      | Laptops          | Ordinateurs portables       | computers |
      | notebooks    | Notebooks        | Ordinateurs ultra-portables | laptops   |
      | apparels     | Apparels & Shoes | Habillements & chaussures   | default   |
      | shirts       | Shirts           | Chemises                    | apparels  |
      | jeans        | Jeans            | Jeans                       | apparels  |
      | shoes        | Shoes            | Chaussures                  | apparels  |
      | shoes_male   | Shoes Male       | Chaussures homme            | shoes     |
      | shoes_female | Shoes Female     | Chaussures femme            | shoes     |
    And the following Magento configuration:
      | property            | value                                              |
      | code                | magento1                                           |
      | label               | Magento Configuration 1                            |
      | soapUsername        | adminsoap                                          |
      | soapApiKey          | adminsoap                                          |
      | soapUrl             | http://magento-connector-magento.ci/api/soap/?wsdl |
      | defaultStoreView    | default                                            |
      | defaultLocale       | en_US                                              |
      | rootCategoryMapping | {"Master": "default"}                              |
      | storeViewMapping    | {}                                                 |
      | attributeMapping    | {}                                                 |
    And I am logged in as "peter"
    And I am on the exports page
    And I create a new export
    When I fill in the following information in the popin:
    | Code  | magento_category_export      |
    | Label | Export categories to Magento |
    | Job   | Magento Category Export      |
    And I press the "Save" button
    And I fill in the following information:
      | Channel               | Magento                 |
      | Magento configuration | Magento Configuration 1 |
    And I press the "Save" button
    Then I launch the export job
    And I wait for the "magento_category_export" job to finish
    Then I check if "categories" were sent to Magento:
      | store_view         | text                        | parent                    | root             |
      | Default Store View | Computers                   | Default Category          | Default Category |
      | Default Store View | Desktops                    | Computers                 |                  |
      | Default Store View | Laptops                     | Computers                 |                  |
      | Default Store View | Notebooks                   | Laptops                   |                  |
      | Default Store View | Apparels & Shoes            | Default Category          |                  |
      | Default Store View | Shirts                      | Apparels & Shoes          |                  |
      | Default Store View | Jeans                       | Apparels & Shoes          |                  |
      | Default Store View | Shoes                       | Apparels & Shoes          |                  |
      | Default Store View | Shoes Male                  | Shoes                     |                  |
      | Default Store View | Shoes Female                | Shoes                     |                  |
      | fr_fr              | Ordinateurs                 | Default Category          | Default Category |
      | fr_fr              | Ordinateurs de bureau       | Ordinateurs               |                  |
      | fr_fr              | Ordinateurs portables       | Ordinateurs               |                  |
      | fr_fr              | Ordinateurs ultra-portables | Ordinateurs portables     |                  |
      | fr_fr              | Habillements & chaussures   | Default Category          |                  |
      | fr_fr              | Chemises                    | Habillements & chaussures |                  |
      | fr_fr              | Jeans                       | Habillements & chaussures |                  |
      | fr_fr              | Chaussures                  | Habillements & chaussures |                  |
      | fr_fr              | Chaussures homme            | Chaussures                |                  |
      | fr_fr              | Chaussures femme            | Chaussures                |                  |
