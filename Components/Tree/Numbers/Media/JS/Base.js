/**
 * Update tree lines
 *
 * @param string tree_id
 */
function numbers_tree_update_lines(tree_id) {
	$('.numbers_tree_option_table_level_nextchild', $('#' + tree_id)).each(function (k, v) {
		var height = $(v).parent('.numbers_tree_option_table_level').height();
		$(v).css({'height': height});
		$(v).height(height);
	});
	$('.numbers_tree_option_table_level_last', $('#' + tree_id)).each(function (k, v) {
		var height = $(v).parent('.numbers_tree_option_table_level').height();
		$(v).css({'height': height});
		$(v).height(height);
	});
}
