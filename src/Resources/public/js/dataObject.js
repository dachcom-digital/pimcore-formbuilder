'use strict';

/**
 * Dependencies
 */

// True if Object
function _isObject(value) {
    return typeof(value) === 'object' && value !== null;
}

// True if Array
function _isArray(value) {
    return Array.isArray(value);
}

/// True if type is string
function _isString(value) {
    return typeof(value) === 'string';
}

// True if undefined
function _isUndefined(value) {
    return typeof(value) === 'undefined';
}

// True if Number
function _isNumber(value) {
    return typeof(value) === 'number';
}

// True if Boolean
function _isBoolean(value) {
    return typeof(value) === 'boolean';
}

// True if Date object
function _isDate(value) {
    return value instanceof Date;
}

function DataObjectParser($data){
    this._data = $data || {};
}

/**
 * Given a dot deliminated string set will create an object
 * based on the structure of the string with the desired value
 *
 * @param {[String} $path  path indicating where value should be placed
 * @param {Mixed} $value   the value desired to be set at the location determined by path
 */
DataObjectParser.prototype.set = function($path, $value) {
    if(!$path || $path==='') return void 0;

    var _self = this;
    var re = /[\$\w-|]+|\[\]|([^\[[\w]\]]|["'](.*?)['"])/g;
    // parse $path on dots, and brackets
    var pathList = $path.match(re);
    var parent = this._data;
    var parentKey;
    var grandParent = null;
    var grandParentKey = null;

    var addObj = function($obj, $key, $data) {
        if($key === '[]') {
            $obj.push($data);
        } else {
            $obj[$key] = $data;
        }
    };

    while(pathList.length > 0) {
        parentKey = pathList.shift().replace(/["']/g, '');

        // Number, treat it as an array
        if (!isNaN(+parentKey) || parentKey === "[]") {
            if(!_isArray(parent)  /* prevent overwritting */ ) {
                parent = [];
                addObj(grandParent, grandParentKey, parent);
            }

            // String, treat it as a key
        } else if (_isString(parentKey)) {
            if(!_isObject(parent)) {
                parent = {};
                addObj(grandParent, grandParentKey, parent);
            }
        }
        // Next
        grandParent = parent;
        grandParentKey = parentKey;
        parent = parent[parentKey];
    }

    addObj(grandParent, grandParentKey, $value);
    return this;
};

/**
 * Returns the value defined by the path passed in
 *
 * @param  {String} $path string leading to a desired value
 * @return {Mixed}        a value in an object
 */
DataObjectParser.prototype.get = function($path) {
    var data = this._data;
    var regex = /[\$\w-|]+|\[\]|([^\[[\w]\]]|["'](.*?)['"])/g;
    //check if $path is truthy
    if (!$path) return void 0;
    //parse $path on dots and brackets
    var paths = $path.match(regex);
    //step through data object until all keys in path have been processed
    while (data !== null && paths.length > 0) {
        if(data.propertyIsEnumerable(paths[0].replace(/"/g, ''))){
            data = data[paths.shift().replace(/"/g, '')];
        }
        else{
            return undefined;
        }
    }
    return data;
};

DataObjectParser.prototype.data = function($data) {
    if(!_isUndefined($data)) {
        this._data = $data;
        return this;
    }
    return this._data;
};

/**
 * "Transposes" data; receives flat data and returns structured
 *
 * @param  {Object}           $data Structured object
 * @return {DataObjectParser} An instance of a DataObjectParser
 */
DataObjectParser.transpose = function($flat) {
    var parser = (new DataObjectParser());
    for(var n in $flat) {
        if($flat[n]!==undefined) {
            parser.set(n, $flat[n]);
        }
    }
    return parser;
};

/**
 * "Untransposes" data object; opposite of transpose
 *
 * @param  {Mixed}  $structured A Object or a DataObjectParser
 * @return {Object}             Flat object
 */
DataObjectParser.untranspose = function($structured) {
    //check to see if $structured is passed
    $structured = $structured || {};
    //handles if an object or a dataObjectParser is passed in
    var structuredData = $structured._data || $structured;

    var traverse = function($data, $isIndex) {
        var result = [];

        var createMapHandler = function($name, $data) {
            return function($item, $i) {
                var name = $name;
                //check if $name is a key of form "hello.world"
                if((/\./).test($name)) name = '["'+name+'"]';
                //add name to $item.key
                $item.key.unshift(name+".");
                //return $item.key with updated key
                return {
                    key: $item.key,
                    data: $item.data
                };
            };
        };

        for(var name in $data) {
            var modifiedName;
            // check if current name is an arrays index
            if($isIndex) modifiedName = "["+name+"]";
            else modifiedName = name;

            // check if current name is linked to a value
            if(_isString($data[name]) || _isNumber($data[name]) || $data[name]===null || _isBoolean($data[name]) || _isDate($data[name])) {
                if((/\./).test(name)) modifiedName = '["'+name+'"]';
                result.push({
                    key: [modifiedName],
                    data: $data[name]
                });
            }

            // check if current name is an array
            else if(_isArray($data[name])) {
                // tell traverse next name is an array's index
                var subArray = traverse($data[name],true);
                result = result.concat(subArray.map(createMapHandler(modifiedName, $data)));
            }

            //check if current name is an object
            else if(_isObject($data[name])) {
                var subObject = traverse($data[name],false);
                result = result.concat(subObject.map(createMapHandler(modifiedName, $data)));
            }
        }
        return result;
    };

    var flatArray = traverse(structuredData,false);
    var flatObj = {};

    flatArray.every(function($item) {
        //check for any dots followed by brackets and remove the dots
        for(var i = 0;i<$item.key.length-1;i++){
            var name = $item.key[i];
            var nextName = $item.key[i+1];
            if((/^\[/).test(nextName)){
                $item.key[i] = name.replace(/\.$/,"");
            }
        }
        //join all the keys in flatArray to form one key
        flatObj[$item.key.join("")] = $item.data;
        return true;
    });
    return flatObj;
};