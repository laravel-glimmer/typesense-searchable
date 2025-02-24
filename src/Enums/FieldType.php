<?php

namespace Glimmer\TypesenseSearchable\Enums;

use Glimmer\TypesenseSearchable\Traits\EnumCasesAsArray;

/**
 * Field types supported and extracted from Typesense documentation.
 *
 * @see https://typesense.org/docs/28.0/api/collections.html#field-types
 */
enum FieldType: string
{
    use EnumCasesAsArray;

    /** Special type that automatically attempts to infer the data type based on the documents added to the collection */
    case Auto = 'auto';
    /** String values */
    case String = 'string';
    /** Array of strings */
    case StringArray = 'string[]';
    /** Special type that automatically converts values to a string or string[] */
    case StringAuto = 'string*';
    /** Integer values up to 2,147,483,647 */
    case Int32 = 'int32';
    /** Array of `int32` */
    case Int32Array = 'int32[]';
    /** Integer values larger than 2,147,483,647 */
    case Int64 = 'int64';
    /** Array of `int64` */
    case Int64Array = 'int64[]';
    /** Floating point / decimal numbers */
    case Float = 'float';
    /** Array of floating point / decimal numbers */
    case FloatArray = 'float[]';
    /** `true` or `false` */
    case Bool = 'bool';
    /** Array of booleans */
    case BoolArray = 'bool[]';
    /** Latitude and longitude specified as `[lat, lng]` */
    case GeoPoint = 'geopoint';
    /** Arrays of Latitude and longitude specified as `[[lat1, lng1], [lat2, lng2]]` */
    case GeoPointArray = 'geopoint[]';
    /** Geographic polygon defined by an array of coordinates specified as `[lat1, lng1, lat2, lng2, ...]`. Latitude/longitude pairs must be in counter-clockwise (CCW) or clockwise (CW) order */
    case GeoPolygon = 'geopolygon';
    /** Nested objects */
    case Object = 'object';
    /** Arrays of nested objects */
    case ObjectArray = 'object[]';
    /** Special type that is used to indicate a base64 encoded string of an image used for Image search */
    case Image = 'image';
}
