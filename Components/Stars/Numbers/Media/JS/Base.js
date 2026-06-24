/**
 * Numbers stars object
 *
 * @param string id
 * @param int star
 * @param int number_of_stars
 */
var numbers_start_item_click = (id, star, number_of_stars) => {
	$('#' + id).val(star);
	for (var i = 1; i <= number_of_stars; i++) {
		if (i <= star) {
			$('#' + id + '_star_' + i).removeClass('numbers_stars_active').addClass('numbers_stars_selected');
		} else {
			$('#' + id + '_star_' + i).removeClass('numbers_stars_selected').addClass('numbers_stars_active');
		}
	}
};
