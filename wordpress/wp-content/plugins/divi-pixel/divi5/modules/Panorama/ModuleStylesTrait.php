<?php
namespace DIPI\Modules\Panorama;

if (!defined("ABSPATH")) {
    die("Direct access forbidden.");
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Text\TextStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Border\BorderStyle;
use ET\Builder\Packages\Module\Options\Spacing\SpacingStyle;

trait ModuleStylesTrait
{
    use CustomCssTrait;
    use StyleDeclarationTrait;
    private static $props = [];

    public static function getAttrByMode($attrs, $attr, $default = null, $mode = null) {
		return (((($attrs??[])[$attr]??[])['innerContent']??[])[$mode??'desktop']??[])['value']??$default??'';
	}

    public static function getAttr(
        $attrs,
        $attr,
        $default = null,
        $zoom = "",
        $unit = "",
        $wrap_func = ""
    ) {
        $AttrValue = (($attrs ?? [])[$attr] ?? [])["innerContent"] ?? [
            "desktop" => ["value" => $default ?? ""],
        ];
        return $AttrValue;
    }

    public static function module_styles($args)
    {
        $attrs    = $args['attrs'] ?? [];
        $elements = $args['elements'];
        $settings = $args['settings'] ?? [];
        $order_class  = $args['orderClass'] ?? '';
        $order_number = preg_replace('/[^0-9]/', '', $order_class);

        $type = static::getAttrByMode($attrs, 'type');
        $img_src = static::getAttrByMode($attrs, 'image2d');
        $is_2d_image = $type == '2D Image' && !empty($img_src);

        if($is_2d_image) {
            $img_size = getimagesize($img_src);
            $repeat = static::getAttrByMode($attrs, 'repeat');
            $width = ($repeat === 'Horizontal' || $repeat === 'Both') ? $img_size[0] * 3 . 'px' : $img_size[0] . 'px';
            $height = ($repeat === 'Vertical' || $repeat === 'Both') ? $img_size[1] * 3 . 'px' : $img_size[1] . 'px';
        }

        Style::add(
			[
				'id'            => $args['id'],
				'name'          => $args['name'],
				'orderIndex'    => $args['orderIndex'],
				'storeInstance' => $args['storeInstance'],
				'styles'        => [
                    // Module.
					$elements->style([
                        'attrName'   => 'module',
                        'styleProps' => [
                            'disabledOn' => [
                                'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
                            ],
                        ],
					]),
                    CssStyle::style([
                        'selector'  => $args['orderClass'],
                        'attr'      => $attrs['css'] ?? [],
                        'cssFields' => static::custom_css(),
                    ]),
                    $elements->style([
                        'attrName'  => 'overlay_bg',
                    ]),
                    $is_2d_image ? CommonStyle::style([
                        'selector'            => "$order_class .dipi-panorma-image2d .dipi-img-drag",
                        'attr'                => static::getAttr($attrs, 'image2d'),
                        'declarationFunction' => function ( array $args ) use ($width, $height) {
                            $img_src = $args['attrValue'];
                            return  "position: absolute;background-image:url($img_src);width:$width; height:$height;transform: translate(-50%,-50%);z-index:1;cursor: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAA7MK6iAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAodJREFUeNq0Vz2IGkEUnjPXCqYyWG2njWChkO6MVTpzYJFCyKW08mzsJBe0P9MIaSQBm1RqZxo1hZXCHdholassBIlok8583/JW9sy57u7tPvjYH4b53pv35nszZ8qBxePxBB5pIAQ8AJ3pdLpWLuzMJiGJ2kJ6aJ9BfuOU+IVN0gHwmhECBeA78AvQgPfhcDi0XC5/OiEO2BhzC3CJS4wOKAKfhPQSuAeu4WDaCfG5jTHvZHJGe8f8RiIRtVgsSHQh5L+BD8DQk4ilmLjUXXEgVK/XVa/XU5lMRknO1+KY5vVSG0YHDEIVjUaN/wk3VW1JjGq9l4iyJ+bRZHt5muNvwNUJB196GrFMWnIzsRcRn7IBilDJUl9Kevwn5tZiwXW7XW273bbhxFByToGpH5PUZxPXajWVTCb191arRcKrYDCo4IS+z+HIF6n8jnk1Al7ljGS0crmsRqOReZ+3Renu4MS1JTEGaCIeji0Wiz3a59lsVjWbTeP7VrT/MTHJgIFIID38Y/bSjZGYqTCExxCc8ycaQpqDWTQomBA0mV5eHAoEI0ilUvvIDr/5PplMzApnqc27XC63M2yz2eyKxeKO/w24tUajYcyRfirHnfl8riqVyr5g2BRYMH4LyEfuQSyxnodqtar/zOfz+tKNx2PPiAMH8sjN/oZtDuT7yI0c0gFfiE+Re2mBI43Bd/KARVfyi3xt5yDgGTl3i+lwYasfP5scIqT6/b6SA6O9czUNZ+a/ODv/wOtbeP6KE5kk0NLQpVShUFCr1YqfBcz1YJvYLTnHkFSWmb35q6MrzJGbRYJ6Tl2nuJhtNpvpZEyNGElLju9ORxy4kVtFyGLYUO5W/x30/wkwAGYcYOCgmyR/AAAAAElFTkSuQmCC), auto;";
                        }
                    ]) : null,
                    $is_2d_image ? CommonStyle::style([
                        'selector'            => "$order_class .dipi-panorma-image2d .dipi-img-drag img",
                        'attr'                => static::getAttr($attrs, 'image2d'),
                        'declarationFunction' => function ( array $args ) {
                            return  "display:block; max-width:inherit";
                        }
                    ]) : null,
                    $is_2d_image ? CommonStyle::style([
                        'selector'            => "$order_class .dipi-panorama-overlay.light",
                        'attr'                => static::getAttr($attrs, 'image2d'),
                        'declarationFunction' => function ( array $args ) {
                            return  "color:#fff";
                        }
                    ]) : null,
                    $is_2d_image ? CommonStyle::style([
                        'selector'            => "$order_class .dipi-panorama-overlay.dark",
                        'attr'                => static::getAttr($attrs, 'image2d'),
                        'declarationFunction' => function ( array $args ) {
                            return  "color:#000";
                        }
                    ]) : null,
                    $is_2d_image ? CommonStyle::style([
                        'selector'            => "$order_class .dipi-hand-icon",
                        'attr'                => static::getAttr($attrs, 'image2d'),
                        'declarationFunction' => function ( array $args ) {
                            return  "width: 25px;height: 30px;background-size: 100% 100%;	background-image: url('data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyMy4xLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgdmlld0JveD0iMCAwIDM5Ni41IDQ2OS4zIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCAzOTYuNSA0NjkuMzsiIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPHN0eWxlIHR5cGU9InRleHQvY3NzIj4NCgkuc3Qwe2ZpbGw6I0ZGRkZGRjtzdHJva2U6IzAwMDAwMDtzdHJva2UtbWl0ZXJsaW1pdDoxMDt9DQo8L3N0eWxlPg0KPGc+DQoJPGc+DQoJCTxwb2x5Z29uIGNsYXNzPSJzdDAiIHBvaW50cz0iMTMsMjc0LjggMTg2LjgsNDQ4LjYgMjkzLjksNDQ4LjYgMzQwLjUsNDQ4LjYgMzYyLjMsNDI2LjggMzgxLjMsMzg0LjYgMzg1LjYsMjk2LjIgMzg1LjYsMjI0LjkgDQoJCQkzNTguNSwyMDYuOSAzMjIuNCwyMDQgMjk3LjIsMTgxLjIgMjY2LjMsMTg1LjUgMjMyLjEsMTY0LjYgMTk4LjIsMTYxLjcgMTkwLjMsNzIuNCAxNTgsNTUuMiAxMzAuNCw3MC45IDEzMS40LDEzNC42IDEyOSwxODQgDQoJCQkxMjkuOSwyMTUuNCAxMjkuNSwyMzQuNiAxMjcuNiwyNTguNyAxMjYuMSwyNzcuNyA0OS42LDI0MC4xIDE4LjcsMjQ1LjMgCQkiLz4NCgkJPHBhdGggZD0iTTM1My44LDE5MmMtOC44LDAtMTYuOSwyLjctMjMuNyw3LjJjLTUuOC0xNi42LTIxLjctMjguNS00MC4zLTI4LjVjLTguOCwwLTE2LjksMi43LTIzLjcsNy4yDQoJCQljLTUuOC0xNi42LTIxLjctMjguNS00MC4zLTI4LjVjLTcuOCwwLTE1LjEsMi4xLTIxLjMsNS43Vjg1LjNjMC0yMy41LTE5LjEtNDIuNy00Mi43LTQyLjdzLTQyLjcsMTkuMS00Mi43LDQyLjd2MTgxLjNMODIsMjM4LjgNCgkJCWMtMjItMTYuNS01My4zLTE0LjMtNzIuNyw1LjJjLTEyLjUsMTIuNS0xMi41LDMyLjgsMCw0NS4ybDE1MS45LDE1MS45YzE4LjEsMTguMSw0Mi4yLDI4LjEsNjcuOSwyOC4xaDUwDQoJCQljNjQuNywwLDExNy4zLTUyLjYsMTE3LjMtMTE3LjNWMjM0LjdDMzk2LjUsMjExLjEsMzc3LjQsMTkyLDM1My44LDE5MnogTTM3NS4yLDM1MmMwLDUyLjktNDMuMSw5Ni05Niw5NmgtNTANCgkJCWMtMTkuOSwwLTM4LjctNy44LTUyLjgtMjEuOUwyNC41LDI3NC4yYy00LjItNC4yLTQuMi0xMC45LDAtMTUuMWM2LjYtNi42LDE1LjQtMTAsMjQuMy0xMGM3LjIsMCwxNC40LDIuMiwyMC41LDYuOGw1NC4xLDQwLjYNCgkJCWMzLjIsMi40LDcuNiwyLjgsMTEuMiwxczUuOS01LjUsNS45LTkuNVY4NS4zYzAtMTEuOCw5LjYtMjEuMywyMS4zLTIxLjNjMTEuOCwwLDIxLjMsOS42LDIxLjMsMjEuM3YxNjBjMCw1LjksNC44LDEwLjcsMTAuNywxMC43DQoJCQlzMTAuNy00LjgsMTAuNy0xMC43VjE5MmMwLTExLjgsOS42LTIxLjMsMjEuMy0yMS4zYzExLjgsMCwyMS4zLDkuNiwyMS4zLDIxLjN2NTMuM2MwLDUuOSw0LjgsMTAuNywxMC43LDEwLjcNCgkJCWM1LjksMCwxMC43LTQuOCwxMC43LTEwLjd2LTMyYzAtMTEuOCw5LjYtMjEuMywyMS4zLTIxLjNzMjEuMyw5LjYsMjEuMywyMS4zdjMyYzAsNS45LDQuOCwxMC43LDEwLjcsMTAuN3MxMC43LTQuOCwxMC43LTEwLjcNCgkJCXYtMTAuN2MwLTExLjgsOS42LTIxLjMsMjEuMy0yMS4zczIxLjMsOS42LDIxLjMsMjEuM0wzNzUuMiwzNTJMMzc1LjIsMzUyeiIvPg0KCTwvZz4NCjwvZz4NCjxnPg0KCTxnPg0KCQk8cGF0aCBkPSJNMTYxLjgsMGMtNDcuMSwwLTg1LjMsMzguMy04NS4zLDg1LjNjMCwxNC4yLDMuNywyOC4xLDExLDQxLjNjMiwzLjUsNS42LDUuNSw5LjMsNS41YzEuNywwLDMuNS0wLjQsNS4yLTEuMw0KCQkJYzUuMS0yLjksNy05LjQsNC4xLTE0LjVjLTUuNS05LjktOC4zLTIwLjQtOC4zLTMwLjljMC0zNS4zLDI4LjctNjQsNjQtNjRzNjQsMjguNyw2NCw2NGMwLDUuMS0wLjgsMTAuNC0yLjUsMTYuNw0KCQkJYy0xLjYsNS43LDEuOCwxMS41LDcuNSwxMy4xYzUuNiwxLjYsMTEuNS0xLjgsMTMuMS03LjVjMi4yLTguMiwzLjMtMTUuMywzLjMtMjIuNEMyNDcuMiwzOC4zLDIwOC45LDAsMTYxLjgsMHoiLz4NCgk8L2c+DQo8L2c+DQo8L3N2Zz4NCg==')";
                        }
                    ]) : null,
                    $is_2d_image ? CommonStyle::style([
                        'selector'            => "$order_class .dipi-scroll-icon",
                        'attr'                => static::getAttr($attrs, 'image2d'),
                        'declarationFunction' => function ( array $args ) {
                            return  "background-size: 100% 100%;      margin-right: 8px!important; width: 30px;height: 40px;	background-image: url('data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyMy4xLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgdmlld0JveD0iMCAwIDUxMiA1MTIiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDUxMiA1MTI7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtmaWxsOiNGRkZGRkY7fQ0KPC9zdHlsZT4NCjxlbGxpcHNlIGNsYXNzPSJzdDAiIGN4PSIyNTUuNiIgY3k9IjI1NC45IiByeD0iMTY3LjUiIHJ5PSIyMzYiLz4NCjxnPg0KCTxnPg0KCQk8cGF0aCBkPSJNMjU2LDBDMTU2LjYsMCw3NS43LDgyLjEsNzUuNywxODMuMXYxNDUuOGMwLDEwMSw4MC45LDE4My4xLDE4MC4zLDE4My4xYzk5LjQsMCwxODAuMy04MS45LDE4MC4zLTE4Mi41VjE4My4xDQoJCQlDNDM2LjMsODIuMSwzNTUuNCwwLDI1NiwweiBNNDAyLjQsMzI5LjVjMCw4Mi02NS43LDE0OC42LTE0Ni40LDE0OC42Yy04MC43LDAtMTQ2LjQtNjYuOS0xNDYuNC0xNDkuMlYxODMuMQ0KCQkJYzAtODIuMyw2NS43LTE0OS4yLDE0Ni40LTE0OS4yYzgwLjcsMCwxNDYuNCw2Ni45LDE0Ni40LDE0OS4yVjMyOS41eiIvPg0KCTwvZz4NCjwvZz4NCjxnPg0KCTxnPg0KCQk8cGF0aCBkPSJNMjU2LDE0MC4xYy05LjQsMC0xNyw3LjYtMTcsMTd2NTkuM2MwLDkuNCw3LjYsMTcsMTcsMTdjOS40LDAsMTctNy42LDE3LTE3di01OS4zQzI3MywxNDcuNywyNjUuNCwxNDAuMSwyNTYsMTQwLjF6Ii8+DQoJPC9nPg0KPC9nPg0KPC9zdmc+DQo=')";
                        }
                    ]) : null,
                    $is_2d_image ? CommonStyle::style([
                        'selector'            => "$order_class .dipi-panorama-icon",
                        'attr'                => static::getAttr($attrs, 'image2d'),
                        'declarationFunction' => function ( array $args ) {
                            return  "display: inline-block;vertical-align: middle;margin: 5px;";
                        }
                    ]) : null,
				],
			]
		);
    }
}
