var grid;
var store;
var selModel;

    window.setInterval(function() {
        grid.getView().refresh();
        grid.store = store;
        store.load();
    }, 6000);

    window.setInterval(function() {
        nowplaying();
    }, 2000);

function nowplaying() {
    Ext.Ajax.request({
        url: 'ajax.php?action=nowplaying',
        success: function(response, opts) {
            var obj = Ext.decode(response.responseText);
            var md = obj['metadata'];
            Ext.get('nowplaying').update('<h4>Now Playing:</h4> ' + md['title'].trim());
        },
        failure: function(response, opts) {
            //console.log('server-side failure with status code ' + response.status);
        }
    });
}

Ext.onReady(function(){

    // Fill the now playing div
    nowplaying();

///////////////////////////////////////////////////////
// Submit song form
///////////////////////////////////////////////////////
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

/*
        // Reset and Submit buttons
        buttons: [{
            text: 'Reset',
            handler: function() {
                this.up('form').getForm().reset();
            }
        }, { */
        buttons: [{
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

///////////////////////////////////////////////////////
// Playlist grid panel
///////////////////////////////////////////////////////
    Ext.define('Queue', {
        extend: 'Ext.data.Model',
        fields: [
            {name: 'order', type: 'integer'},
            {name: 'queue_id', type: 'integer'},
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
                idProperty: 'order'
            }
        }
    });
 
//alert('butts');
    // load data from the url
    store.load();
 
    selModel = Ext.create('Ext.selection.CheckboxModel', {
        mode: 'SINGLE',
        allowDeselect: true,
        listeners: {
            selectionchange: function(sm, selections) {
                grid.down('#removeButton').setDisabled(selections.length === 0);
            }
        }
    });

    // create the Grid
    //var grid = new Ext.grid.GridPanel({
    grid = new Ext.grid.GridPanel({
        store: store,
        columns: [
            {id:'order',header: 'Position', width: 70, dataIndex: 'order'},
            //{header: 'Queue ID', dataIndex: 'queue_id', hideable: false},
            {header: 'Queue ID', dataIndex: 'queue_id', hidden: true, hideable: false},
            {header: 'Ready', width: 60, dataIndex: 'ready'},
            {header: 'URL', width: 600, dataIndex: 'url'},
            {header: 'Requestor', width: 100, dataIndex: 'requestor'},
            {header: 'Status', dataIndex: 'error'}
        ],

        columnLines: true,
        selModel: selModel,

        dockedItems: [{
            xtype: 'toolbar',
            items: [{
                text:'&#9650;',
                tooltip:'Move song up in the queue',
                iconCls:'up'
            }, '-', {
                text:'&#9660;',
                tooltip:'Move song down in the queue',
                iconCls:'down'
            },'-',{
                itemId: 'removeButton',
                text:'Delete Song',
                tooltip:'Remove the selected item',
                iconCls:'remove',
                disabled: true,
                handler: function(){
                    //Ext.Msg.alert('Click', 'Delete ID #');
                    //Ext.Msg.alert('Click', 'Delete ID #' + stuff);

/*
                    var row = selModel.getLastSelected().data.queue_id;
                    var alert_msg = 'Delete ID #' + row.toString();
                    Ext.Msg.alert('Click', alert_msg);
*/

                    var row = selModel.getLastSelected().data.queue_id;
                    Ext.Ajax.request({
                        url: 'ajax.php',
                        params: {
                            action: 'delete',
                            row: row
                        },
                        success: function(response){
                            var text = response.responseText;
                            // process server response here
                        }
                    });
                }
            }]
        }],

//                text:'&#8593;',
// TO THE TOP &#x21C9;
// TO THE BOTTOM &#x21CB;

        stripeRows: true,
        height: 500,
        width: '100%',
        title: 'Playlist Queue'
    });
 
    // render grid
    grid.render('queue');
 
});
