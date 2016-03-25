// ACHTUNG: Dieses Kommentar in der 1. Zeile NICHT entfernen. Shopware hat einen Bug, der beim Laden des Plugins
// die erste Zeile entfernt.
// Der Name dieser Datei sollte dem Namen der CSS-Klasse des Emotion-Elements entsprechen, siehe Bootstrap::CLS

// Der Name des definierten ExtJS-Elements sollte eindeutig sein und in keinem weiteren Plugin verwendert werden.
Ext.define('Shopware.apps.Emotion.view.components.fields.InstagramElementComboBox', {
    extend: 'Ext.form.field.ComboBox', // hier bestimmen wir, dass es sich bei unserem custom Feld um ein Dropdown handelt.
    alias: 'widget.instagram-element-limit', // ACHTUNG: Dieser Name muss exakt dem X-Type unseres Elements entsprechen, siehe Bootstrap::LIMIT_XTYPE
    name: 'limit', // beliebiger, treffender Name unseres Elements

    initComponent: function() {
        Ext.apply(this, {
            fieldLabel: this.fieldLabel,
            displayField: 'display',
            valueField: 'value',
            queryMode: 'local',
            triggerAction: 'all',
            store: this.createStore(), // Diese Funktion liefert den "Store" zurück, ein ExtJS-Objekt mit unseren Werten
            listeners: {
                afterrender: function (box) { // Erstes Element vorauswählen, wenn nicht bereits eines angewählt wurde.
                    if (!box.value) {
                        var store = box.getStore();
                        box.setValue(store.getAt(0));
                    }
                }
            }
        });

        this.callParent(arguments);
    },

    createStore: function() {
        return Ext.create('Ext.data.Store', {
            fields: ['value', 'display'],
            data : [
                {
                    value: 0,
                    display: 'unbegrenzt'
                },
                {
                    value: 5,
                    display: '5'
                },
                {
                    value: 10,
                    display: '10'
                },
                {
                    value: 15,
                    display: '15'
                },
                {
                    value: 20,
                    display: '20'
                },
                {
                    value: 25,
                    display: '25'
                }
            ]
        });
    }

});