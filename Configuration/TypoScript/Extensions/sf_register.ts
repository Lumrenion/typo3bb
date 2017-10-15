plugin.tx_sfregister {
    settings {
        validation.create {
            username >
            username {
                50 = LumIT\Typo3bb\Extensions\SfRegister\Validation\Validator\UniqueValidator(global = 1)
                51 = LumIT\Typo3bb\Extensions\SfRegister\Validation\Validator\NoEmailAddressValidator
            }
            email >
            email {
                50 = LumIT\Typo3bb\Extensions\SfRegister\Validation\Validator\UniqueValidator(global = 1)
            }
        }
        validation.edit {
            displayName {
                50 = LumIT\Typo3bb\Extensions\SfRegister\Validation\Validator\UniqueValidator(global = 1)
                51 = LumIT\Typo3bb\Extensions\SfRegister\Validation\Validator\NoEmailAddressValidator
            }
            email >
            email {
                50 = LumIT\Typo3bb\Extensions\SfRegister\Validation\Validator\UniqueValidator(global = 1)
            }
        }
    }
}