<?php
namespace IsoCodes;
class SwiftBic implements IsoCodeInterface
{
    /**
     * SWIFT-BIC validator
     * @author Sensson
     * @param  string  $swiftbic
     * @return boolean
     */
	public static function validate( $swiftbic ) {
		$regexp = "([a-zA-Z]{4}[a-zA-Z]{2}[a-zA-Z0-9]{2}([a-zA-Z0-9]{3})?)";
		return (boolean) preg_match( $regexp, $swiftbic );
	}
}
