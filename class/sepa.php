<?php
/*
############################################################
# clsGenerateSepaXML.php
#
# Class to generate XML for SEPA transactions
#
# Copyright:		Sensson
# Project:			Sepa
# Platform:			PHP 5
# Date created : 	16-05-2013
# History:			02-06-2013
					- building of array moved to clsGenerateSepaXML
					- building gruoup header moved to clsGenerateSepaXML
					- output xml to screen, via header to downloadable text file
#
############################################################
*/

class clsGenerateSepaXML {
	private $m_sResult;
	private $m_sError;
	private $m_sLocation;
	private $m_sXMLFile;
	private $m_aPaymentInfo;
	private $m_aPayments;
	private $m_aGroupHeader;
	private $m_nTotalTransactions;
	private $m_nTotalAmount;

	// a more flexible approach
	private $aPayments;
	private $aPaymentsInfo;
	private $aTotalAmount;
	private $aTotalTransactions; 

	// Contructor
	public function __construct() {
		$this->m_sResult = $this->m_sError = "";
		$this->m_sLocation = "tmp/";
		$this->m_sXMLFile = "";
		$this->m_nTotalTransactions = $this->m_nTotalAmount = 0;
		$this->m_aPaymentInfo = $this->m_aPayments = $this->m_aGroupHeader = array();
	}

	// Destructor
	public function __destruct() {
	}

//*******************************************************
// GET FUNCTIONS
//*******************************************************

	//########################################
	// common public getter, $p_sVarName determines the return value
	// returns variable value or empty string if $p_sVarName is not recognized
	//########################################
	public function __get($p_sVarName) {
        return match ($p_sVarName) {
            "result" => $this->m_sResult,
            "error" => $this->m_sError,
            default => "",
        };
    }

//*******************************************************
// SET FUNCTIONS
//*******************************************************
	
	//########################################
	// common public setter, $p_sVarName determines the return value
	// returns true on succes, false if $p_sVarName is not recognized
	//########################################
	public function __set($p_sVarName, $p_vValue) {
		
		switch ($p_sVarName) {
			case "location":
				$this->m_sLocation = $p_vValue;
				return true;
			case "xmlfile":
				$this->m_sXMLFile = $p_vValue;
				return true;
		}
		
		return false;
	}

	
//*******************************************************
// PUBLIC FUNCTIONS
//*******************************************************

    /**
     * @throws Exception
     */
    public function addPayment($p_aPayment = array(), $batchType = 'RCUR')
    {
		// start to build payment info for sepa payment message, all info below is needed for 1 payment
		if($batchType != "FRST" AND $batchType != "RCUR") {
			throw new Exception("clsGenerateSepaXML::addPayment - batchType needs to be either FRST or RCUR!");
		}
		
		// count total amount for groupheader
		$this->m_nTotalAmount += $p_aPayment["PayAmount"];
		
		// count number of transactions for group header
		$this->m_nTotalTransactions++;
		
		// batch specific settings
		$this->aTotalAmount[$batchType] += $p_aPayment["PayAmount"];
		$this->aTotalTransactions[$batchType]++;
		
		// sub array PaymentTypeInformation
		$aPaymentTypeInfo = array("InstrPrty" => "",								//InstructionPriority, optional
								  "SvcLvl" => array("Cd" => "SEPA"),				//ServiceLevel, optional
								  "LclInstrm" => array("Cd" => "CORE"),				//LocalInstrument, optional
								  "SeqTp" => $batchType,								//SequenceType, optional, used here to fill PmtTpInf tag
								  "CtgyPurp" => "",									//CategoryPurpose, optional
		);
		// sub array Creditor
		$aCreditor = array("Nm" => $p_aPayment["CrName"],							//Creditor Name, optional string(70)
						   "PstlAdr" => "",											//PostalAddress, optional
						   "Id" => "",												//Identification, optional
						   "CtryOfRes" => "",										//CountryOfResidence, optional
						   "CtctDtls" => "",										//ContactDetails, optional
		);	
		// sub array CreditorAccount												
		$aCreditorAccount = array("Id" => array("IBAN" => $p_aPayment["IbanCr"]),	//Identification
								  "Tp" => "",										//Type, optional
								  "Ccy" => "",										//Currency, optional
								  "Nm" => "",										//Name, optional
		);
		// sub array CreditorAgent
		$aCrAgent = array("FinInstnId" => array("BIC" => $p_aPayment["BicCr"]),		//FinancialInstitutionIdentification
						  "BrnchId" => "",											//BranchIdentification, optional
		);
		
		// 4th level sub array Identification for CreditorSchemeIdentification
		$aCreditorOther = array("Id" => $p_aPayment["CreditorID"],
								"SchmeNm" => array("Prtry" => "SEPA"),
		);
		// 3rd level sub array Identification for CreditorSchemeIdentification
		$aCreditorID = array("PrvtId" => array("Othr" => $aCreditorOther));
		//2nd array CreditorSchemeIdentification
		$aCredSchemeId = array("Nm" => $p_aPayment["CreditorName"],					//Name, optional
							   "PstlAdr" => "",										//PostalAddress, optional
							   "Id" => $aCreditorID,								//Identification, optional
							   "CtryOfRes" => "",									//CountryOfResidence, optional
							   "CtctDtls" => "",									//ContactDetails, optional
		);
		// 2nd level sub array PaymentIdentification for DirectDebitTransactionInformation
		$aPmtId = array("InstrId" => "",											//2.30 InstructionIdentification, optional string(35)
						"EndToEndId" => $p_aPayment["UniqueIdentifier"],			//2.31 EndToEndIdentification, string(35). Unique identifier
		);

		// 3rd level sub array for MandateRelatedInformation in DirectDebitTransaction
		$aMndtRltdInf = array("MndtId" => $p_aPayment["MandateID"],					//2.48 MandateIdentification, required
							  "DtOfSgntr" => $p_aPayment["MandateDateSig"],			//2.49 DateOfSignature, required
							  "AmdmntInd" => "false",								//2.50 AmendmentIndicator, true or false
							  //"AmdmntInfDtls" => $aAmdmntInfDtls,					//2.51 AmendmentInformationDetails, optional
							  "ElctrncSgntr" => "",									//2.62 ElectronicSignature, optional
							  "FrstColltnDt" => "",									//2.63 FirstCollectionDate, optional
							  "FnlColltnDt" => "",									//2.64 FinalCollectionDate, optional
							  "Frqcy" => "",										//2.65 Frequency, optional
		);
		// 3rd level sub array for CreditorSchemeIdentification in DirectDebitTransaction
// NOT ALLOWED TO USE DOUBLE WITH CdtrSchmeId ON TRANSACTION LEVEL!
		//$aCdtrSchmeId = array("Nm" => "",											//Name, optional
		//					  "PstlAdr" => "",										//PostalAddress, optional
		//					  "Id" => "",											//Identification, optional
		//					  "CtryOfRes" => "",									//CountryOfResidence, optional
		//					  "CtctDtls" => "",										//ContactDetails, optional
		//);
		// 2nd level sub array DirectDebitTransaction for DirectDebitTransactionInformation
		$aDDTransaction = array("MndtRltdInf" => $aMndtRltdInf,						//2.47 MandateRelatedInformation
								//"CdtrSchmeId" => $aCdtrSchmeId,						//2.66 CreditorSchemeIdentification, optional
								"PreNtfctnId" => "",								//2.67 PreNotificationIdentification, optional, not used yet in SEPA for NL
								"PreNtfctnDt" => "",								//2.68 PreNotificationDate, optional, not used yet in SEPA for NL
		);

		// 2nd level sub array DebtorAgent for DirectDebitTransactionInformation
		$aDbtrAgt = array("FinInstnId" => array("BIC" => $p_aPayment["BicDb"]),		//FinancialInstitutionIdentification
						  "BrnchId" => "",											//BranchIdentification, optional
		);
		// 2nd level sub array UltimateCreditor for DirectDebitTransactionInformation
		$aDbtr = array("Nm" => $p_aPayment["DbtrName"],								//Name, mandatory
					   "PstlAdr" => "",												//PostalAddress, optional
					   "Id" => "",													//Identification, optional
					   "CtryOfRes" => "",											//CountryOfResidence, optional
					   "CtctDtls" => "",											//ContactDetails, optional
		);
		// 2th level sub array for DebtorAccount in DirectDebitTransaction
		$aDbtrAcct = array("Id" => array("IBAN" => $p_aPayment["IbanDb"]),			//Identification
						   "Tp" => "",												//Type, optional
						   "Ccy" => "",												//Currency, optional
						   "Nm" => "",												//Name, optional
		);
		// 2nd level sub array Purpose for DirectDebitTransactionInformation, fillt with 2.77 code
		$aPurpose = array("Cd" => "SUPP",											//2.77 Code via external purpose code list
						  "Prtry" => "",											//2.78, not used in SEPA for NL
		);
		// 2nd level sub array RemittanceInformation for DirectDebitTransactionInformation, fill with 2.77 code
		$aRmtInf = array ("Ustrd" => $p_aPayment["InfoUnstruct"],					//2.89 Unstructured, optional, string (140)
		);
		
		// sub array DirectDebitTransactionInformation
		$aDDTransactionInfo = array("PmtId" => $aPmtId,								//2.29 PaymentIdentification
									"PmtTpInf" => "",								//2.32 PaymentTypeInformation, optional, not used yet in SEPA for NL
									"InstdAmt" => $p_aPayment["PayAmount"],			//2.44 InstructedAmount
									"ChrgBr" => "SLEV",								//2.45 ChargeBearer, optional. SLEV = FollowingServiceLevel
									"DrctDbtTx" => $aDDTransaction,					//2.46 DirectDebitTransaction, optional
									//"UltmtCdtr" => $aUltmtCdtr,						//2.69 UltimateCreditor, optional
									"DbtrAgt" => $aDbtrAgt,							//2.70 DebtorAgent
									"DbtrAgtAcct" => "",							//2.71 DebtorAgentAccount, optional, not used yet in SEPA for NL
									"Dbtr" => $aDbtr,								//2.72 Debtor
									"DbtrAcct" => $aDbtrAcct,						//2.73 DebtorAccount
									//"UltmtDbtr" => $aUltmtDbtr,						//2.74 UltimateDebtor, optional
									"InstrForCdtrAgt" => "",						//2.75 InstructionForCreditorAgent, optional, not used yet in SEPA for NL
									"Purp" => $aPurpose,							//2.76 Purpose, optional, 2.77 code or 2.78 Proprietary (2.78 not used yet in SEPA for NL)
									"RgltryRptg" => "",								//2.79 RegulatoryReporting, optional, not used yet in SEPA for NL
									"Tax" => "",									//2.80 Tax, optional, not used yet in SEPA for NL
									"RltdRmtInf" => "",								//2.81 RelatedRemittanceInformation, optional, not used yet in SEPA for NL
									"RmtInf" => $aRmtInf,							//2.88 RemittanceInformation, optional, 2.89 Ustrd or 2.90 Strd (2.90 not used yet in SEPA for NL)
		);
		
		// main payment array with all information, including all above sub arrays
		// the bellow is necessary to fix a bug
		$CdtrSchmeId = $aCredSchemeId;
		unset($CdtrSchmeId['Nm']);
		
		$aPaymentInfo = array("PmtInfId" => "{$batchType}-" . $p_aPayment["UniqueID"],				//2.1 PaymentInformationIdentification, string(35), unique identifacation for payment group
							  "PmtMtd" => "DD",										//2.2 PaymentMethod, Code: DD = DirectDebit
							  "BtchBookg" => "",									//2.3 BatchBooking, optional, true for batch handling, false for transaction handling
							  "NbOfTxs" => $this->aTotalTransactions[$batchType],	//2.4 NumberOfTransactions, optional int(15)
							  "CtrlSum" => $this->aTotalAmount[$batchType],			//2.5 ControlSum, optional decimal (17,1)
							  "PmtTpInf" => $aPaymentTypeInfo,						//2.6 PaymentTypeInformation
							  "ReqdColltnDt" => $p_aPayment["PayDate"],				//2.18 RequestedCollectionDate, ISODate
							  "Cdtr" => $aCreditor,									//2.19 Creditor
							  "CdtrAcct" => $aCreditorAccount,						//2.20 CreditorAccount
							  "CdtrAgt" => $aCrAgent,								//2.21 CreditorAgent
							  "CdtrAgtAcct" => "",									//2.22 CreditorAgentAccount, not used yet in SEPA for NL
							  //"UltmtCdtr" => $aUltimateCreditor,					//2.23 UltimateCreditor, optional, not used if direct debit!
							  "ChrgBr" => "",										//2.24 ChargeBearer, optional
							  "ChrgsAcct" => "",									//2.25 ChargesAccount, not used yet in SEPA for NL
							  "ChrgsAcctAgt" => "",									//2.26 ChargesAccountAgent, not used yet in SEPA for NL
							  "CdtrSchmeId" => $CdtrSchmeId,						//2.27 CreditorSchemeIdentification, optional
							  "DrctDbtTxInf" => "{addpayments}", 					//2.28 DirectDebitTransactionInformation, was $aDDTransactionInfo befor						  
		);  // end of payment info
		
		$this->aPayments[$batchType][] = $aDDTransactionInfo;
		$this->aPaymentsInfo[$batchType] = $aPaymentInfo;
		
	}

    /**
     * @throws Exception
     */
    public function generateXML($p_aInitiatingParty, $p_sMessageID): bool|string
    {
		if ((!is_array($p_aInitiatingParty)) && (count($p_aInitiatingParty) < 1)) {
			throw new Exception("clsGenerateSepaXML::generateXML - p_aInitiatingParty cannot be empty!");
		}
		
		// build group header
		$aGroupHeader = array("MsgId" => $p_sMessageID,			 					//1.1 MessageIdentification, string(35). unique identification of message
							  "CreDtTm" => date("c"),								//1.2 CreationDateTime, ISODateTime of creation of file
							  "Authstn" => "",										//1.3 Authorisation, not used yet in SEPA for NL
							  "NbOfTxs" => $this->m_nTotalTransactions,				//1.6 NumberOfTransactions, (15) Number of individual transactions contained in the message
							  "CtrlSum" => $this->m_nTotalAmount,					//1.7 ControlSum, optional decimal(17,1) Total of all individual amounts included in the message
							  "InitgPty" => $p_aInitiatingParty,					//1.8 InitiatingParty, This can either be the creditor or a party that initiates the direct debit on behalf of the creditor
							  "FwdgAgt" => "",										//1.9 ForwardingAgent, not used yet in SEPA for NL
		);
		
		// build xml object
		$sXMLString = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		$sXMLString .= "<Document xmlns=\"urn:iso:std:iso:20022:tech:xsd:pain.008.001.02\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" />";
		$objXML = new SimpleXMLElement($sXMLString);
		$objCstmrDrctDbtInitn = $objXML->addChild("CstmrDrctDbtInitn");
		
		
		// check if groupheader exists, if given build xml tags for all given elements
		if ((is_array($aGroupHeader)) && (count($aGroupHeader) > 0)) {
			$objGrpHdr = $objCstmrDrctDbtInitn->addChild("GrpHdr");
			foreach ($aGroupHeader as $sKey => $vValue) {
				if (is_array($vValue)) {
					// value is array itself, loop through all elements and create childs
					$objNewChild = $objGrpHdr->addChild($sKey);
					foreach ($vValue as $sChildKey => $vChildValue) {
						if (strlen($vChildValue) > 0) {
			    			$objNewChild->addChild($sChildKey, utf8_encode($vChildValue));
			    		}
					}
				} elseif (strlen($vValue) > 0) {
					// normal string/number as value, create child with value
					$objGrpHdr->addChild($sKey, utf8_encode($vValue));
				}
			}
		} else {
			throw new Exception("clsGenerateSepaXML::generateXML - aGroupHeader cannot be empty!");
		}
		
		foreach($this->aPayments as $aBatchType=> $aPayments) {
			if ((is_array($aPayments)) && (count($aPayments) > 0)) {
				$objPmtInf = $objCstmrDrctDbtInitn->addChild("PmtInf");
				$this->data2XML($this->aPaymentsInfo[$aBatchType], $objPmtInf);
			}
		}
		
		// create xml
		return $objXML->asXML();
		
		/*
		// check for payments, build payment tag per given block of payment info
		if ((is_array($this->m_aPayments)) && (count($this->m_aPayments) > 0)) {
			foreach ($this->m_aPayments as $aPayment) {
				// every payment has separate block of xml tags, add to xml dom
				$objPmtInf = $objCstmrDrctDbtInitn->addChild("PmtInf");
				$this->data2XML($aPayment, $objPmtInf);
			}
			*/
		
	}

	private function data2XML($p_aData, $p_objXML)
    {
		foreach($p_aData as $sKey => $vValue) {
			
			// check if vValue is array, if so, add as child recursive
			if (is_array($vValue)) {
				// create child with sKey as name
				$objNode = $p_objXML->addChild($sKey);
				
                //aad array to child in xml dom
                $this->data2Xml($vValue, $objNode);
                
            } elseif (strlen($vValue) > 0) {	
            	if ($vValue == "{addpayments}") {
					if ((is_array($this->aPayments[$p_aData['PmtTpInf']['SeqTp']])) && (count($this->aPayments[$p_aData['PmtTpInf']['SeqTp']]) > 0)) {
						foreach($this->aPayments[$p_aData['PmtTpInf']['SeqTp']] as $aPaymentData) {
							// every payment has separate block of xml tags, add to xml dom
							$objDrctDbtTxInf = $p_objXML->addChild("DrctDbtTxInf");
							$this->data2XML($aPaymentData, $objDrctDbtTxInf);
						}
					}					            
            	} else {            	
	            	// vValue is normal text, add child
	                $objChild = $p_objXML->addChild($sKey, $vValue);
					if ($sKey == "InstdAmt") {
						$objChild->addAttribute("Ccy", "EUR");
					}
            	}
            }
        }

    }
}

