/**
 * Math
 *
 * @type object
 */
numbers.math = {

	/**
	 * Scale
	 *
	 * @var int
	 */
	scale: 2,

	/**
	 * Set Scale
	 *
	 * @param int scale
	 */
	set_scale: function(scale) {
		scale = intval(scale.toString());
		this.scale = scale;
		bcscale(scale)
	},

	/**
	 * Compare
	 *
	 * @param string arg1
	 * @param string arg2
	 * @param int scale
	 * @returns int -1, 0, 1
	 */
	compare: function(arg1, arg2, scale) {
		if (typeof(scale) === 'undefined') {
			scale = this.scale;
		} else if (typeof scale !== 'number') {
			scale = intval(scale.toString());
		}
		return bccomp(arg1, arg2, scale);
	},

	/**
	 * Add
	 *
	 * @param string arg1
	 * @param string arg2
	 * @param int scale
	 * @returns string
	 */
	add: function(arg1, arg2, scale) {
		if (typeof(scale) === 'undefined') {
			scale = this.scale;
		} else if (typeof scale !== 'number') {
			scale = intval(scale.toString());
		}
		return bcadd(arg1, arg2, scale);
	},

	/**
	 * Subtract
	 *
	 * @param string arg1
	 * @param string arg2
	 * @param int scale
	 * @returns string
	 */
	subtract: function(arg1, arg2, scale) {
		if (typeof(scale) === 'undefined') {
			scale = this.scale;
		} else if (typeof scale !== 'number') {
			scale = intval(scale.toString());
		}
		return bcsub(arg1, arg2, scale);
	},

	/**
	 * Multiply
	 *
	 * @param string arg1
	 * @param string arg2
	 * @param int scale
	 * @returns string
	 */
	multiply: function(arg1, arg2, scale) {
		if (typeof(scale) === 'undefined') {
			scale = this.scale;
		} else if (typeof scale !== 'number') {
			scale = intval(scale.toString());
		}
		return bcmul(arg1, arg2, scale);
	},

	/**
	 * Double the scale
	 *
	 * @param mixed scale
	 * @returns integer
	 */
	double: function(scale) {
		return (intval(scale) * 2) + 1;
	},

	/**
	 * Round
	 *
	 * @param string arg1
	 * @param int scale
	 * @returns string
	 */
	round: function(arg1, scale) {
		if (typeof(scale) === 'undefined') {
			scale = this.scale;
		} else if (typeof scale !== 'number') {
			scale = intval(scale.toString());
		}
		return bcround(arg1, scale);
	},

	/**
	 * Floor
	 *
	 * @param string arg1
	 * @returns string
	 */
	floor: function(arg1) {
		if (arg1[0] != '-') {
			return bcadd(arg1, '0', 0);
		} else {
			return bcsub(arg1, '1', 0);
		}
	},

	/**
	 * Ceil
	 *
	 * @param string arg1
	 * @returns string
	 */
	ceil: function(arg1) {
		if (arg1[0] != '-') {
			return bcadd(arg1, '1', 0);
		} else {
			return bcsub(arg1, '0', 0);
		}
	},

	/**
	 * Abs
	 *
	 * @param string arg1
	 * @returns string
	 */
	abs: function(arg1) {
		return arg1.replace('-', '');
	},

	/**
	 * Zero
	 *
	 * @param int scale
	 * @returns string
	 */
	zero: function(scale) {
		if (typeof(scale) === 'undefined') {
			scale = this.scale;
		} else if (typeof scale !== 'number') {
			scale = intval(scale.toString());
		}
		return this.add('0', '0.0000000000000', scale);
	}
};