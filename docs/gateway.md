Vanaf versie 4.2 is het mogelijk om klanten zelf te laten kiezen voor
automatische incasso. Dit doen we met behulp van een eigen payment gateway.
Wanneer de payment gateway wordt gekozen voor een nieuwe of bestaande factuur
controleert de payment gateway of het mandaat volledig is. Zodra de payment
gateway een fout ontdekt krijgt de klant een melding en kan de klant direct
een nieuw mandaat ondertekenen. 

* Upload sepaincasso.php naar modules/gateways/.
* Activeer de payment gateway via Setup > Payment > Payment Gateways en kies
  voor Incasso / SEPA direct debit. 'Show on Order Form' kan geactiveerd blijven. 
* Zorg er voor dat de oude Direct Debit gateway niet meer zichtbaar is voor
  gebruikers door 'Show on Order Form' uit te schakelen. De incasso module is
  backwards compatible.
  