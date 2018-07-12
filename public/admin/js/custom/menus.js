var menu_node;

$(document).ready(function(){

	//Pages list
    $('#jstree-default').jstree({
        "core": {
            "themes": {
                "responsive": false
            }            
        },
        "types": {
            "default": {
                "icon": "fa fa-folder text-warning fa-lg"
            },
            "file": {
                "icon": "fa fa-file text-inverse fa-lg"
            }
        },
        "plugins": ["types"]
    });

    $('#jstree-default').on('select_node.jstree', function(e,data) { 
        var link = $('#' + data.selected).find('a');

        if($('#tree-item-span-' + link.find('span').attr("data-id") ).length) {
            console.log('Already added');
            return;
        }

        menu_node.create_node(null, '<span id="tree-item-span-' + link.find('span').attr("data-id") + '" data-id="' + link.find('span').attr("data-id") + '">'+link.find('span').html()+'<span class="remover"><i class="fa fa-remove"></i></span></span>' );
        $('.remover').off('click').click( handleMenuRemovers );
    });

    //Menu list
    menu_node = $.jstree.create("#jstree-drag-and-drop", {
        "core": {
            "themes": {
                "responsive": false
            }, 
            "check_callback": true,
        },
        "plugins": [ "dnd" ]
    });

    $('#jstree-drag-and-drop').on('select_node.jstree', function(e,data) { 
        var link = $('#' + data.selected).find('a');
        $('.remover').off('click').click( handleMenuRemovers );
    });

    var handleMenuRemovers = function(e) {
        var id = $(this).closest('li').attr('id');
        console.log(id);
        menu_node.delete_node(id);
        e.preventDefault();
        e.stopPropagation();
        $('.remover').off('click').click( handleMenuRemovers );
    }

    $('.remover').off('click').click( handleMenuRemovers );

    //Submitting
    $('#update-btn').click( function() {
        var items = menu_node.get_node('#');
        var menu = [];
        for(var i in items.children) {
            var node_obj = menu_node.get_node( items.children[i] );
            var node_id = $(node_obj.text).attr('data-id');
            var node = {
                id: node_id,
                children: []
            }


            for(var j in node_obj.children) {
                var subnode_obj = menu_node.get_node( node_obj.children[j] );
                var subnode_id = $(subnode_obj.text).attr('data-id');
                node.children.push(subnode_id);
            }

            menu.push(node);
        }

        $.ajax({
            url     : $('#menu-update').attr('action'),
            type    : $('#menu-update').attr('method'),
            data    : {
                menu: menu,
                _token: $('#menu-update').find("input[name='_token']").val()
            },
            dataType: 'json',
            success : function( res ) {
                ajax_action = false;
                if(res && res.success) {
                    window.location.href = res.href;
                } else {
                    $('#error-message').html('').show();

                    for(var i in res.messages) {
                        $('#error-message').append(res.messages[i]+'<br/>');
                    }
                }
            },
            error : function( data ) {
                ajax_action = false;
                $('#error-message').html('Network Error').show();
            }
        });


    });
});