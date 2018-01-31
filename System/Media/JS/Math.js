/**
 * Math
 *
 * @type object
 */
Numbers.Math = {

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
	setScale: function(scale) {
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
	 * Is equal
	 *
	 * @param string arg1
	 * @param string arg2
	 * @param int scale
	 * @returns bool
	 */
	isEqual: function(arg1, arg2, scale) {
		if (typeof(scale) === 'undefined') {
			scale = 13;
		} else if (typeof scale !== 'number') {
			scale = intval(scale.toString());
		}
		return (this.compare(arg1, arg2, scale) == 0);
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
	 * Divide
	 *
	 * @param string arg1
	 * @param string arg2
	 * @param int scale
	 * @returns string
	 */
	divide: function(arg1, arg2, scale) {
		if (typeof(scale) === 'undefined') {
			scale = this.scale;
		} else if (typeof scale !== 'number') {
			scale = intval(scale.toString());
		}
		return bcdiv(arg1, arg2, scale);
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
	 * @param int scale
	 * @returns string
	 */
	floor: function(arg1, scale) {
		if (typeof(scale) === 'undefined') {
			scale = this.scale;
		} else if (typeof scale !== 'number') {
			scale = intval(scale.toString());
		}
		if (arg1[0] != '-') {
			return bcadd(arg1, '0', scale);
		} else {
			var value = '1';
			if (scale != 0) {
				value = this.divide('1', Math.pow(10, scale).toString(), 2);
			}
			return bcsub(arg1, value, scale);
		}
	},

	/**
	 * Ceil
	 *
	 * @param string arg1
	 * @param int scale
	 * @returns string
	 */
	ceil: function(arg1, scale) {
		if (typeof(scale) === 'undefined') {
			scale = this.scale;
		} else if (typeof scale !== 'number') {
			scale = intval(scale.toString());
		}
		if (arg1[0] != '-') {
			var value = '1';
			if (scale != 0) {
				value = this.divide('1', Math.pow(10, scale).toString(), 2);
			}
			return bcadd(arg1, value, scale);
		} else {
			return bcsub(arg1, '0', scale);
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