<?php

function my_login_logo()
{ ?>
    <style type="text/css">
        #login h1 a,
        .login h1 a {
            background-image: url('<?php echo THEME_ASSETS_URI ?>img/ph-logo.svg');
            height: 100px;
            width: 200px;
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
            padding-bottom: 2rem;
        }

        #loginform {
            border: solid 1px #d9ab4d;
            border-radius: 16px 16px 10px 10px;
            position: relative;
        }
        
        #loginform::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 12px;
            bottom: 0;
            left: 0;
            background-color: #d9ab4d;
        }

    </style>
<?php }
add_action('login_enqueue_scripts', 'my_login_logo');
