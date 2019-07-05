<?php
/**
 * Incasso direct debit helper class
 *
 * Support is available via info@sensson.net.
 * 
 * @author		Sensson <info@sensson.net>
 * @copyright	2004-2017 Sensson
 * @license		This software is furnished under a license and may be used and copied
 * 				only  in  accordance  with  the  terms  of such  license and with the
 * 				inclusion of the above copyright notice.  This software  or any other
 * 				copies thereof may not be provided or otherwise made available to any
 * 				other person.  No title to and  ownership of the  software is  hereby
 * 				transferred. 
 * 				   
 */

include_once("isocodes/IsoCodeInterface.php");
include_once("isocodes/Iban.php");
include_once("isocodes/SwiftBic.php");

class Incasso {
	/**
	 * Generate a list of countries with the ISO code as key and full country name as value
	 *
	 * @return array
	 */
	public function generateCountryList ($whmcsVersion) {
		if ($whmcsVersion == 6) {
			$countries_file = dirname(__FILE__) . '/../../../../includes/countries.php';
			require($countries_file);
			
			// No need to rebuild array. Version 6 is formatted as expected.
			$country_list = $countries;
		}
		if ($whmcsVersion == 7) {
			$countries_file = dirname(__FILE__) . '/../../../../resources/country/dist.countries.json';
			$countries = json_decode(file_get_contents($countries_file), true);

			// Rebuild array as:
			// key:   2 character ISO code
			// value: Full country name
			foreach ($countries as $country_code => $country_details) {
				$country_list[$country_code] = $country_details['name'];
			}
		}

		return $country_list;
	}

	/**
	 * Create a mandate reference
	 *
	 * @return string
	 */
	public function generateMandateRef ($clientId, $currentReference, $referenceSetting) {
		if ($referenceSetting == 9999 || strlen($currentReference) == 0) {
			return "CID-${clientId}";
		} else {
			return $currentReference;
		}
	}

	/**
	 * Check if the bank holder is valid
	 *
	 * @return array
	 */
	public function validateBankHolder ($bankHolder) {
		if (strlen($bankHolder) == 0) {
			return array(
					'error' => true,
					'message' => 'is not valid name (empty).',
				);
		}
		return array('error' => false);
	}

	/**
	 * Check if the city of the bank account is valid
	 *
	 * @return array
	 */
	public function validateBankAccountCity($customerBankAccountCity) {
		if (strlen($customerBankAccountCity) == 0) {
			return array(
					'error' => true,
					'message' => 'is not a valid city (empty)'
			);
		}
		return array('error' => false);
	}

	/**
	 * Check if the IBAN is valid
	 *
	 * @return array
	 */
	public function validateIban ($iban) {
		if (!IsoCodes\Iban::validate($iban)) {
			return array(
					'error' => true,
					'message' => "${iban} is not a valid IBAN number.",
				);
		}
		return array('error' => false);
	}

	/**
	 * Check if the BIC is valid
	 *
	 * @return array
	 */
	public function validateBic ($bic) {
		if (!IsoCodes\SwiftBic::validate($bic)) {
			return array(
					'error' => true,
					'message' => "${bic} is not a valid BIC number.",
				);
		}
		return array('error' => false);
	}

	/**
	 * Check if the date that was used to signed the mandate is valid
	 *
	 * @return array
	 */
	public function validateSigningDate ($signingDate) {
		if (!preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}/', $signingDate)) {
			return array(
					'error' => true,
					'message' => "${signingDate} is not a valid signing date for this direct debit mandate.",
				);
		}
		return array('error' => false);
	}

	/**
	 * Format a date that can be used for SEPA
	 *
	 * @return string
	 */
	public function getTodaysSepaDate () {
		return date('Y-m-d');
	}

	/**
	 * Normalize IBAN and BIC data
	 *
	 * - Everything should be in uppercase
	 * - All non-required spaces should be removed
	 *
	 * @return string
	 */
	public function normalizeData ($data) {
		$data = preg_replace('/\s/', '', $data);
		$data = strtoupper($data);
		return $data;
	}
}
?>