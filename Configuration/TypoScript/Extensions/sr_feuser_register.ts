plugin.tx_srfeuserregister_pi1 {
    create.evalValues {
        username := addToList(uniqueDisplayName)
    }
    edit.evalValues {
        tx_typo3bb_display_name < plugin.tx_srfeuserregister_pi1.create.evalValues.username
    }
}
plugin.tx_srfeuserregister {
    _LOCAL_LANG {
        en {
            evalErrors_uniqueDisplayName_username = This username already exists. Please enter a different username.
            evalErrors_uniqueDisplayName_tx_typo3bb_display_name = This display name already exists. Please enter a different display name.
            evalErrors_uniqueDisplayName_username_informal < .evalErrors_uniqueDisplayName_username
            evalErrors_uniqueDisplayName_tx_typo3bb_display_informal < .evalErrors_uniqueDisplayName_tx_typo3bb_display
        }
    }
}