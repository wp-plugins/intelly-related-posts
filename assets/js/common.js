/**
 * Created by alessio on 15/04/2015.
 */
jQuery(function() {
    jQuery(".irp-hideShow").click(function () {
        irp_hideShow(this);
    });
    jQuery(".irp-hideShow").each(function () {
        irp_hideShow(this);
    });

    jQuery(".irpTags").select2({
        placeholder: "Type here..."
        , theme: "classic"
        , width: '300px'
    });

    //mostra o nasconde un div collegato ad una checkbox
    function irp_hideShow(v) {
        var $source = jQuery(v);
        if ($source.attr('irp-hideIfTrue') && $source.attr('irp-hideShow')) {
            var $destination = jQuery('[name=' + $source.attr('irp-hideShow') + ']');
            if ($destination.length == 0) {
                $destination = jQuery('#' + $source.attr('irp-hideShow'));
            }
            if ($destination.length > 0) {
                var isChecked = $source.is(":checked");
                var hideIfTrue = ($source.attr('irp-hideIfTrue').toLowerCase() == 'true');

                if (isChecked) {
                    if (hideIfTrue) {
                        $destination.hide();
                    } else {
                        $destination.show();
                    }
                } else {
                    if (hideIfTrue) {
                        $destination.show();
                    } else {
                        $destination.hide();
                    }
                }
            }
        }
    }

    if(jQuery('.irp-help').qtip) {
        jQuery('.irp-help').qtip({
            position: {
                corner: {
                    target: 'topMiddle',
                    tooltip: 'bottomLeft'
                }
            },
            show: {
                when: {
                    event: 'mouseover'
                }
            },
            hide: {
                fixed: true,
                when: {
                    event: 'mouseout'
                }
            },
            style: {
                tip: 'bottomLeft',
                name: 'green'
            }
        });
    }
});