<?php

	/**
	 * ConfigurationController.php - This controller offers actions that aid in turning the TFA
	 * service on and off, as well as re-generate the user's secret.  It is used within the action
	 * panel in the system configuration panel in Magento.
	 * @version         1.0.0
	 * @package         JetRails® TwoFactor
	 * @category        Controllers
	 * @author          Rafael Grigorian - JetRails®
	 * @copyright       JetRails®, all rights reserved
	 */
	class JetRails_TwoFactor_ConfigurationController extends Mage_Adminhtml_Controller_Action {

		/**
		 * This action enables the TFA given that TFA is currently disabled and the passed TOTP pin
		 * is valid, thus proving that the user has successfully set up their TFA account.
		 * @return      void
		 */
		public function enableAction () {
			// Get the user id using the Mage session
			$uid = Mage::getSingleton ("admin/session")->getUser ()->getUserId ();
			// Initialize the Data helper class and the TOTP helper class
			$Data = Mage::helper ("twofactor/Data");
			$TOTP = Mage::helper ("twofactor/TOTP");
			// Check to see if it is enabled and verified first
			if ( !$Data->isEnabled ( $uid ) ) {
				// Save the passed pin
				$pin = Mage::app ()->getRequest ()->getParam ("pin");
				// Initialize the TOTP helper class with secret
				$TOTP->initialize ( $Data->getSecret ( $uid ) );
				// Make sure that the pins match
				if ( $pin == $TOTP->pin () ) {
					// Set enable flag to true
					$Data->setEnabled ( $uid, true );
					// Set a success message
					Mage::getSingleton ("core/session")->addSuccess (
						"Successfully enabled two factor authentication."
					);
				}
				// Otherwise if the pin is incorrect
				else {
					// Set a notice message
					Mage::getSingleton ("core/session")->addError (
						"Failed to enable two factor authentication, due to an invalid pin."
					);
				}
			}
			// Otherwise, it is enabled already
			else {
				// Set a notice message
				Mage::getSingleton ("core/session")->addError (
					"Two factor authentication is already enabled."
				);
			}
			// Go back to the referrer page
			$this->_redirectReferer ();
		}

		/**
		 * This action disables TFA for the current logged in user, given the fact that TFA is
		 * currently enabled and that passed TOTP pin is valid, thus proving that it is the
		 * authorized user requesting to disable this service.
		 * @return      void
		 */
		public function disableAction () {
			// Get the user id using the Mage session
			$uid = Mage::getSingleton ("admin/session")->getUser ()->getUserId ();
			// Initialize the Data helper class, the TOTP helper class, and the Cookie class
			$Cookie = Mage::helper ("twofactor/Cookie");
			$Data = Mage::helper ("twofactor/Data");
			$TOTP = Mage::helper ("twofactor/TOTP");
			// Check to see if it is enabled and verified first
			if ( $Data->isEnabled ( $uid ) ) {
				// Save the passed pin
				$pin = Mage::app ()->getRequest ()->getParam ("pin");
				// Initialize the TOTP helper class with secret
				$TOTP->initialize ( $Data->getSecret ( $uid ) );
				// Make sure that the pins match
				if ( $pin == $TOTP->pin () ) {
					// Disable all items
					$Data->setEnabled ( $uid, false );
					$Data->setSecret ( $uid, "" );
					// Delete cookie if set
					$Cookie->delete ();
					// Set a success message
					Mage::getSingleton ("core/session")->addSuccess (
						"Successfully disabled two factor authentication."
					);
				}
				// Otherwise if the pin is incorrect
				else {
					// Set a notice message
					Mage::getSingleton ("core/session")->addError (
						"Failed to disable two factor authentication, due to an invalid pin."
					);
				}
			}
			// Otherwise, it is disabled
			else {
				// Set a notice message
				Mage::getSingleton ("core/session")->addError (
					"Two factor authentication is not enabled."
				);
			}
			// Go back to the referrer page
			$this->_redirectReferer ();
		}

		/**
		 * This action simply generated a new secret using the TOTP helper class, given the fact
		 * that the service is currently disabled.
		 * @return      void
		 */
		public function generateAction () {
			// Get the user id using the Mage session
			$uid = Mage::getSingleton ("admin/session")->getUser ()->getUserId ();
			// Initialize the Data helper class and the TOTP helper class
			$Data = Mage::helper ("twofactor/Data");
			$TOTP = Mage::helper ("twofactor/TOTP");
			// Check to see if it is enabled and verified first
			if ( !$Data->isEnabled ( $uid ) ) {
				// Initialize the TOTP class
				$TOTP->initialize ();
				// Save the secret into the database
				$Data->setSecret ( $uid, $TOTP->getSecret () );
				// Set a success message
				Mage::getSingleton ("core/session")->addSuccess (
					"Successfully re-generated two factor authentication secret."
				);
			}
			// Otherwise, it is enabled
			else {
				// Set a notice message
				Mage::getSingleton ("core/session")->addError (
					"Two factor authentication is enabled, cannot re-generate secret at this state."
				);
			}
			// Go back to the referrer page
			$this->_redirectReferer ();
		}

	}

?>