var grid;
var store;

//    window.setInterval(alert('butts'), 5000);
    window.setInterval(function() {
        grid.getView().refresh();
        grid.store = store;
        store.load();
    }, 6000);

Ext.onReady(function(){

    Ext.create('Ext.form.Panel', {
        title: 'Submit Song',
        bodyPadding: 5,
        width: 350,

        // The form will submit an AJAX request to this URL when submitted
        url: 'ajax.php?action=push',

        // Fields will be arranged vertically, stretched to full width
        layout: 'anchor',
        defaults: {
            anchor: '100%'
        },

        // The fields
        defaultType: 'textfield',
        items: [{
            fieldLabel: 'Name',
            name: 'requestor',
            allowBlank: false
        },{
            fieldLabel: 'YouTube URL',
            name: 'url',
            allowBlank: false
        }],

        // Reset and Submit buttons
        buttons: [{
            text: 'Reset',
            handler: function() {
                this.up('form').getForm().reset();
            }
        }, {
        //buttons: [{
            text: 'Submit',
            formBind: true, //only enabled once the form is valid
            disabled: true,
            handler: function() {
                var form = this.up('form').getForm();
                if (form.isValid()) {
                    form.submit({
                        success: function(form, action) {
                           Ext.Msg.alert('Success', action.result.msg);
                           /*grid.getView().refresh();
                           grid.store = store;
                           store.load();*/
                        },
                        failure: function(form, action) {
                            Ext.Msg.alert('Failed', action.result.msg);
                        }
                    });
                }
            }
        }],
        renderTo: 'add'
        //renderTo: Ext.getBody()
    });

    Ext.define('Queue', {
        extend: 'Ext.data.Model',
        fields: [
            {name: 'slot', type: 'integer'},
            {name: 'ready'},
            {name: 'url', type: 'string'},
            {name: 'requestor', type: 'string'},
            {name: 'error', type: 'string'}
         ]
    });
 
    // create the data store
    //var store = new Ext.data.JsonStore({
    store = new Ext.data.JsonStore({
        model: 'Queue',
        proxy: {
            type: 'ajax',
            url: 'ajax.php?action=list',
            reader: {
                type: 'json',
                root: 'queue',
                idProperty: 'slot'
            }
        }
    });
 
//alert('butts');
    // load data from the url
    store.load();
 
    // create the Grid
    //var grid = new Ext.grid.GridPanel({
    grid = new Ext.grid.GridPanel({
        store: store,
        columns: [
            {id:'slot',header: 'Position', width: 70, dataIndex: 'slot'},
            {header: 'Ready', width: 60, dataIndex: 'ready'},
            {header: 'URL', width: 600, dataIndex: 'url'},
            {header: 'Requestor', width: 100, dataIndex: 'requestor'},
            {header: 'Status', dataIndex: 'error'}
        ],
        stripeRows: true,
        height: 600,
        width: '100%',
        title: 'Playlist Queue'
    });
 
    // render grid
    grid.render('queue');
 
});
