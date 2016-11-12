/**
 * Sha1
 *
 * @param string text
 * @returns string
 */
var sha1 = function(text) {
	var obj = new jsSHA('SHA-1', 'TEXT');
	obj.update(text);
	return obj.getHash('HEX');
};