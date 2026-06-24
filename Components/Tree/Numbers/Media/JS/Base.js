/**
 * @var object
 */
const numbers_tree_search_for_text_searches = {};

/**
 * Search for text
 *
 * @param string searchable_class
 * @param string text
 */
function numbers_tree_search_for_text(searchable_class, elem) {
	const text = $(elem).val().trim();
	if (text) {
		numbers_tree_search_for_text_searches[$(elem).closest('form').attr('id')]= true;
		$('.' + searchable_class).each(function (k, v) {
			if ($(v).attr('data-name').toLowerCase().indexOf(text.toLowerCase()) != -1) {
				$(v).closest('.numbers_tree_option_table_tr').show();
				$('.numbers_tree_expand_icon', $(v)).hide();
				$('.numbers_tree_option_table_level', $(v)).hide();
			} else {
				$(v).closest('.numbers_tree_option_table_tr').hide();
			}
		});
	} else {
		if (numbers_tree_search_for_text_searches[$(elem).closest('form').attr('id')]) {
			numbers_tree_search_for_text_searches[$(elem).closest('form').attr('id')] = false;
			$(elem).closest('form').submit();
		}
	}
}
