@account
Feature: My account (without changing login data)

    Background:
        Given I am on the page "Account"
        And   I log in successful as "Max Mustermann" with email "test@example.com" and password "shopware"

    @password
    Scenario Outline: I can't change my password, when something is wrong
        When  I change my password from "<password>" to "<new_password>" with confirmation "<confirmation>"
        Then  I should see "<message>"

    Examples:
        | password  | new_password | confirmation | message                                                                 |
        |           |              |              | Das aktuelle Passwort stimmt nicht!                                     |
        | shopware  |              |              | Bitte wählen Sie ein Passwort welches aus mindestens 8 Zeichen besteht. |
        | shopware  | sw4          | sw4          | Bitte wählen Sie ein Passwort welches aus mindestens 8 Zeichen besteht. |
        | shopware  | shopware4    | shopware5    | Die Passwörter stimmen nicht überein.                                   |
        | shopware4 | shopware5    | shopware5    | Das aktuelle Passwort stimmt nicht!                                     |

    @email
    Scenario Outline: I can't change my email, when something is wrong
        When  I change my email with password "<password>" to "<new_email>" with confirmation "<confirmation>"
        Then  I should see "<message>"

    Examples:
        | password  | new_email         | confirmation      | message                                        |
        |           |                   |                   | Das aktuelle Passwort stimmt nicht!            |
        | shopware  |                   |                   | Bitte geben Sie eine gültige eMail-Adresse ein |
        | shopware  | abc               | abc               | Bitte geben Sie eine gültige eMail-Adresse ein |
        | shopware  | test@example.com  | test2@example.com | Die eMail-Adressen stimmen nicht überein.      |
        | shopware4 | test2@example.com | test2@example.com | Das aktuelle Passwort stimmt nicht!            |

    @shipping
    Scenario Outline: I can change my shipping address
        When I follow "Lieferadresse ändern"
        And  I submit the form "shippingForm" on page "Account" with:
            | field      | register[shipping] |
            | salutation | <salutation>       |
            | company    | <company>          |
            | firstname  | <firstname>        |
            | lastname   | <lastname>         |
            | street     | <street>           |
            | zipcode    | <zipcode>          |
            | city       | <city>             |
            | country    | <country>          |

        Then I should see "Erfolgreich gespeichert"
        And  the "shipping" address should be "<company>, <firstname> <lastname>, <street>, <zipcode> <city>, <country>"

    Examples:
        | salutation | company     | firstname | lastname   | street              | zipcode | city        | country     |
        | ms         |             | Erika     | Musterfrau | Heidestraße 17 c    | 12345   | Köln        | Schweiz     |
        | mr         | shopware AG | Max       | Mustermann | Mustermannstraße 92 | 48624   | Schöppingen | Deutschland |

    @payment
    Scenario Outline: I can change my payment method
        Given the element "AccountPayment" should have the content:
            | position      | content          |
            | currentMethod | <oldPaymentName> |

        When  I change the payment method to <paymentId>
        Then  I should see "Ihre Zahlungsweise wurde erfolgreich gespeichert"
        And   the element "AccountPayment" should have the content:
            | position      | content       |
            | currentMethod | <paymentName> |

    Examples:
        | oldPaymentName | paymentId | paymentName |
        | Vorkasse       | 3         | Nachnahme   |
        | Nachnahme      | 4         | Rechnung    |
        | Rechnung       | 5         | Vorkasse    |

    @payment
    Scenario: I can change my payment method
        When  I change the payment method to 2:
            | field             | value          |
            | sDebitAccount     | 123456789      |
            | sDebitBankcode    | 1234567        |
            | sDebitBankName    | Shopware Bank  |
            | sDebitBankHolder  | Max Mustermann |
        Then  I should see "Ihre Zahlungsweise wurde erfolgreich gespeichert"
        And   the element "AccountPayment" should have the content:
            | position      | content     |
            | currentMethod | Lastschrift |

        When  I change the payment method to 6:
            | field             | value                  |
            | sSepaIban         | DE68210501700012345678 |
            | sSepaBic          | SHOPWAREXXX            |
            | sSepaBankName     | Shopware Bank          |

        Then  I should see "Ihre Zahlungsweise wurde erfolgreich gespeichert"
        And   the element "AccountPayment" should have the content:
            | position      | content |
            | currentMethod | SEPA    |