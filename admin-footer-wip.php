<?php

// ACF: Change the gallery button label in the CMS
add_action('acf/input/admin_footer', 'sdg_acf_admin_footer');
function sdg_acf_admin_footer() {

    ?>
    <script>
    (function($) {

        $('.acf-field-54ae017167b45 a.add-attachment').text('Add/Edit Resources');

    })(jQuery);
    </script>
    <?php

}
