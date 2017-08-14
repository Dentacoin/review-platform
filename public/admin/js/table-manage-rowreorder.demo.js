/*   
Template Name: Color Admin - Responsive Admin Dashboard Template build with Twitter Bootstrap 3.3.5
Version: 1.9.0
Author: Sean Ngu
Website: http://www.seantheme.com/color-admin-v1.9/admin/
*/

var handleDataTableRowReorder = function() {
	"use strict";
    
    if ($('#data-table').length !== 0) {
        $('#data-table').DataTable({
            responsive: true,
            rowReorder: true
        });
    }
};

var TableManageRowReorder = function () {
	"use strict";
    return {
        //main function
        init: function () {
            handleDataTableRowReorder();
        }
    };
}();