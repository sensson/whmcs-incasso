# WHMCS incasso module configuratie

De module maakt gebruik van Custom Client Fields in WHMCS. Deze moeten worden
opgezet in WHMCS, waarna deze geconfigureerd kunnen worden in de Incasso module.

## Custom Client Fields

Ga naar Setup en kies voor Custom Client Fields. Maak de volgende velden aan:

* Field Name: Method of Payment
  * Field Type: Dropdown
  * Select Options: Bank- or gateway payment,Automatic subscription
  * Admin Only: ja
* Field Name: Bank No.
  * Field Type: Text Box
  * Admin Only: ja
* Field Name: Bank Holder
  * Field Type: Text Box
  * Admin Only: ja
* Field Name: Bank City (optioneel)
  * Field Type: Text Box
  * Admin Only: ja

Voor de payment gateway maken we gebruik van een signature veld.

* Field Name: Mandate signature
  * Field Type: Text Box
  * Admin Only: ja

Sinds SEPA zijn hier de volgende velden bijgekomen:

* Field Name: Bank BIC
  * Field Type: Text Box
  * Validation: `/^([a-zA-Z]){4}([a-zA-Z]){2}([0-9a-zA-Z]){2}([0-9a-zA-Z]{3})?$/`
  * Admin Only: ja
* Field Name: Customer Mandate Reference
  * Field Type: Text Box
  * Admin Only: ja
* Field Name: Customer Mandate Date of Signing
  * Field Type: Text Box
  * Description: YYYY-MM-DD
  * Validation: `/[0-9]{4}-[0-9]{2}-[0-9]{2}/`
  * Admin Only: ja

Wij adviseren standaard om admin only in te schakelen. 

* Klik op Addons > Incasso
* Klik op het tabblad Subscription Settings en vul de gegevens* in. Schakel
  SEPA in wanneer u hier van gebruik wilt maken. 
* Klik op Save Changes om de wijziging op te slaan

Natuurlijk kunt u deze velden ook andere namen geven. Zolang het voor u
duidelijk is waar deze voor dienen.

Schakel de Automatische Incasso gateway in:

* Setup > Payment > Payment Gateways;
* Selecteer Incasso / SEPA direct debit.
* Zorg er voor dat 'Show on Order Form' ingeschakeld is.
* Schakel de Direct Debit gateway uit.
* Setup > Payment > Payment Gateways;
* Select Direct Debit from the drop down and click Activate;
* Scroll down and make sure the box that says 'Show on Order Form' is unticked

**LET OP**: bij het verwerken van betalingen die met de incasso module zijn
  gedaan, zullen deze verwerkt moeten worden met de module of handmatig door
  de Direct Debit gateway te selecteren.

## Configuratie velden

* Your bank account: uw eigen rekening nummer. Gebruik uw IBAN nummer wanneer
  u SEPA inschakelt.
* Your BIC code: dit is de BIC code welke hoort bij uw IBAN nummer. Dit veld
  is alleen nodig wanneer u SEPA inschakelt.
* Your bank holder: dit is de naam welke verbonden is aan uw bankrekening
  nummer.
* Your address: het adres van uw bedrijf. Deze wordt weergegeven op het
  mandaat.
* Your postcode: de postcode van uw bedrijf. Deze wordt weergegeven op het
  mandaat.
* Your city: de plaats van uw bedrijf. Deze wordt weergegeven op het mandaat.
* Your country: het land van uw bedrijf. Deze wordt weergegeven op het
  mandaat.
* Your identifier: dit veld wordt gebruikt om uw bestanden te identificeren
  samen met het nummer van de aangeleverde batch.
* Your fixed description: dit veld wordt niet meer gebruikt binnen SEPA.
* Use original invoice value: WHMCS laat het standaard toe om facturen aan te
  passen. Wanneer u deze instelling op 'Yes' in stelt zal de module op alle
  momenten gebruik maken van het originele factuur bedrag.

### SEPA settings

* Enable SEPA: schakel dit in wanneer u klaar bent voor SEPA. Vergeet niet
  voor alle klanten het bankrekening nummer aan te passen waar nodig. De
  module geeft een foutmelding als een nummer incorrect is.
* Creditor ID: dit vindt u terug op het SEPA contract wat u afgesloten heeft
  met uw bank. 
* Customer Mandate Reference: wij adviseren gebruik te maken van Use default
  format of CID-{USERID}. Dit is de referentie van het ondertekende contract
  wat uw klant met u aan gaat op het moment dat SEPA wordt aangeboden. Dit is
  een custom client field.
* Customer Mandate Sign Date: dit is de datum van de ondertekening van het
  contract. Dit is een custom client field.

### Security

* Secure Hash: deze hash wordt gebruikt bij interne functies, bijvoorbeeld het
  ophalen van data binnen WHMCS. Wij raden aan deze regelmatig te wijzigen.

### Custom fields for payment details

* Subscription field: selecteer het custom client field wat gebruikt wordt voor
  automatische incasso. Maak je gebruik van onze payment gateway? Wij raden aan
  om hier Payment method: direct debit (invoices) te selecteren.
* Client Bank Number: selecteer het custom client field wat gebruikt wordt voor
  het rekening nummer van de klant.
* Client BIC Number: selecteer het custom client field wat gebruikt wordt voor
  de BIC code van het IBAN rekening nummer van de klant.
* Client Bank Holder: selecteer het custom client field wat gebruikt wordt om de
  houder van het rekening nummer aan te geven.
* Client Bank City: selecteer het custom client field wat gebruikt wordt om de
  stad van het rekening nummer aan te geven of stel dit in op Use client city om
  de waarde uit de klant gegevens op te halen.
* Client Signature Field: selecteer het custom client field wat gebruikt wordt
  om de handtekening van de klant op te slaan.

### Klant configuratie

Om klanten op te nemen in het incassobestand, dient u deze als zodanig te
configureren. Dit doet u door de klant te openen en te kiezen voor het tabblad
Profile. Hier kunt u de gegevens invullen.
