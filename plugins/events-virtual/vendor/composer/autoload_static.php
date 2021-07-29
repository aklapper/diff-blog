<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb77f7e7ac0d9e44c5d28cb91079fb3cc
{
    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'Tribe\\Events\\Virtual\\' => 21,
        ),
        'D' => 
        array (
            'Defuse\\Crypto\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Tribe\\Events\\Virtual\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src/Tribe',
        ),
        'Defuse\\Crypto\\' => 
        array (
            0 => __DIR__ . '/..' . '/defuse/php-encryption/src',
        ),
    );

    public static $classMap = array (
        'Defuse\\Crypto\\Core' => __DIR__ . '/..' . '/defuse/php-encryption/src/Core.php',
        'Defuse\\Crypto\\Crypto' => __DIR__ . '/..' . '/defuse/php-encryption/src/Crypto.php',
        'Defuse\\Crypto\\DerivedKeys' => __DIR__ . '/..' . '/defuse/php-encryption/src/DerivedKeys.php',
        'Defuse\\Crypto\\Encoding' => __DIR__ . '/..' . '/defuse/php-encryption/src/Encoding.php',
        'Defuse\\Crypto\\Exception\\BadFormatException' => __DIR__ . '/..' . '/defuse/php-encryption/src/Exception/BadFormatException.php',
        'Defuse\\Crypto\\Exception\\CryptoException' => __DIR__ . '/..' . '/defuse/php-encryption/src/Exception/CryptoException.php',
        'Defuse\\Crypto\\Exception\\EnvironmentIsBrokenException' => __DIR__ . '/..' . '/defuse/php-encryption/src/Exception/EnvironmentIsBrokenException.php',
        'Defuse\\Crypto\\Exception\\IOException' => __DIR__ . '/..' . '/defuse/php-encryption/src/Exception/IOException.php',
        'Defuse\\Crypto\\Exception\\WrongKeyOrModifiedCiphertextException' => __DIR__ . '/..' . '/defuse/php-encryption/src/Exception/WrongKeyOrModifiedCiphertextException.php',
        'Defuse\\Crypto\\File' => __DIR__ . '/..' . '/defuse/php-encryption/src/File.php',
        'Defuse\\Crypto\\Key' => __DIR__ . '/..' . '/defuse/php-encryption/src/Key.php',
        'Defuse\\Crypto\\KeyOrPassword' => __DIR__ . '/..' . '/defuse/php-encryption/src/KeyOrPassword.php',
        'Defuse\\Crypto\\KeyProtectedByPassword' => __DIR__ . '/..' . '/defuse/php-encryption/src/KeyProtectedByPassword.php',
        'Defuse\\Crypto\\RuntimeTests' => __DIR__ . '/..' . '/defuse/php-encryption/src/RuntimeTests.php',
        'Tribe\\Events\\Virtual\\Admin_Template' => __DIR__ . '/../..' . '/src/Tribe/Admin_Template.php',
        'Tribe\\Events\\Virtual\\Assets' => __DIR__ . '/../..' . '/src/Tribe/Assets.php',
        'Tribe\\Events\\Virtual\\Compatibility' => __DIR__ . '/../..' . '/src/Tribe/Compatibility.php',
        'Tribe\\Events\\Virtual\\Compatibility\\Event_Tickets\\Email' => __DIR__ . '/../..' . '/src/Tribe/Compatibility/Event_Tickets/Email.php',
        'Tribe\\Events\\Virtual\\Compatibility\\Event_Tickets\\Event_Meta' => __DIR__ . '/../..' . '/src/Tribe/Compatibility/Event_Tickets/Event_Meta.php',
        'Tribe\\Events\\Virtual\\Compatibility\\Event_Tickets\\Service_Provider' => __DIR__ . '/../..' . '/src/Tribe/Compatibility/Event_Tickets/Service_Provider.php',
        'Tribe\\Events\\Virtual\\Compatibility\\Event_Tickets\\Template_Modifications' => __DIR__ . '/../..' . '/src/Tribe/Compatibility/Event_Tickets/Template_Modifications.php',
        'Tribe\\Events\\Virtual\\Compatibility\\Events_Control_Extension\\Meta_Redirection' => __DIR__ . '/../..' . '/src/Tribe/Compatibility/Events_Control_Extension/Meta_Redirection.php',
        'Tribe\\Events\\Virtual\\Compatibility\\Events_Control_Extension\\Service_Provider' => __DIR__ . '/../..' . '/src/Tribe/Compatibility/Events_Control_Extension/Service_Provider.php',
        'Tribe\\Events\\Virtual\\Compatibility\\Filter_Bar\\Events_Virtual_Filter' => __DIR__ . '/../..' . '/src/Tribe/Compatibility/Filter_Bar/Events_Virtual_Filter.php',
        'Tribe\\Events\\Virtual\\Compatibility\\Filter_Bar\\Service_Provider' => __DIR__ . '/../..' . '/src/Tribe/Compatibility/Filter_Bar/Service_Provider.php',
        'Tribe\\Events\\Virtual\\Compatibility\\Online_Event_Extension\\Service_Provider' => __DIR__ . '/../..' . '/src/Tribe/Compatibility/Online_Event_Extension/Service_Provider.php',
        'Tribe\\Events\\Virtual\\Compatibility\\Online_Event_Extension\\Settings' => __DIR__ . '/../..' . '/src/Tribe/Compatibility/Online_Event_Extension/Settings.php',
        'Tribe\\Events\\Virtual\\Context\\Context_Provider' => __DIR__ . '/../..' . '/src/Tribe/Context/Context_Provider.php',
        'Tribe\\Events\\Virtual\\Encryption' => __DIR__ . '/../..' . '/src/Tribe/Encryption.php',
        'Tribe\\Events\\Virtual\\Event_Meta' => __DIR__ . '/../..' . '/src/Tribe/Event_Meta.php',
        'Tribe\\Events\\Virtual\\Export\\Event_Export' => __DIR__ . '/../..' . '/src/Tribe/Export/Event_Export.php',
        'Tribe\\Events\\Virtual\\Export\\Export_Provider' => __DIR__ . '/../..' . '/src/Tribe/Export/Export_Provider.php',
        'Tribe\\Events\\Virtual\\Hooks' => __DIR__ . '/../..' . '/src/Tribe/Hooks.php',
        'Tribe\\Events\\Virtual\\JSON_LD' => __DIR__ . '/../..' . '/src/Tribe/JSON_LD.php',
        'Tribe\\Events\\Virtual\\Meetings\\Api_Response' => __DIR__ . '/../..' . '/src/Tribe/Meetings/Api_Response.php',
        'Tribe\\Events\\Virtual\\Meetings\\Meeting_Provider' => __DIR__ . '/../..' . '/src/Tribe/Meetings/Meeting_Provider.php',
        'Tribe\\Events\\Virtual\\Meetings\\YouTube\\Classic_Editor' => __DIR__ . '/../..' . '/src/Tribe/Meetings/YouTube/Classic_Editor.php',
        'Tribe\\Events\\Virtual\\Meetings\\YouTube\\Connection' => __DIR__ . '/../..' . '/src/Tribe/Meetings/YouTube/Connection.php',
        'Tribe\\Events\\Virtual\\Meetings\\YouTube\\Embeds' => __DIR__ . '/../..' . '/src/Tribe/Meetings/YouTube/Embeds.php',
        'Tribe\\Events\\Virtual\\Meetings\\YouTube\\Event_Meta' => __DIR__ . '/../..' . '/src/Tribe/Meetings/YouTube/Event_Meta.php',
        'Tribe\\Events\\Virtual\\Meetings\\YouTube\\Settings' => __DIR__ . '/../..' . '/src/Tribe/Meetings/YouTube/Settings.php',
        'Tribe\\Events\\Virtual\\Meetings\\YouTube\\Template_Modifications' => __DIR__ . '/../..' . '/src/Tribe/Meetings/YouTube/Template_Modifications.php',
        'Tribe\\Events\\Virtual\\Meetings\\YouTube\\Url' => __DIR__ . '/../..' . '/src/Tribe/Meetings/YouTube/Url.php',
        'Tribe\\Events\\Virtual\\Meetings\\YouTube_Provider' => __DIR__ . '/../..' . '/src/Tribe/Meetings/YouTube_Provider.php',
        'Tribe\\Events\\Virtual\\Meetings\\Zoom\\Abstract_Meetings' => __DIR__ . '/../..' . '/src/Tribe/Meetings/Zoom/Abstract_Meetings.php',
        'Tribe\\Events\\Virtual\\Meetings\\Zoom\\Account_API' => __DIR__ . '/../..' . '/src/Tribe/Meetings/Zoom/Account_API.php',
        'Tribe\\Events\\Virtual\\Meetings\\Zoom\\Api' => __DIR__ . '/../..' . '/src/Tribe/Meetings/Zoom/Api.php',
        'Tribe\\Events\\Virtual\\Meetings\\Zoom\\Classic_Editor' => __DIR__ . '/../..' . '/src/Tribe/Meetings/Zoom/Classic_Editor.php',
        'Tribe\\Events\\Virtual\\Meetings\\Zoom\\Event_Meta' => __DIR__ . '/../..' . '/src/Tribe/Meetings/Zoom/Event_Meta.php',
        'Tribe\\Events\\Virtual\\Meetings\\Zoom\\Meetings' => __DIR__ . '/../..' . '/src/Tribe/Meetings/Zoom/Meetings.php',
        'Tribe\\Events\\Virtual\\Meetings\\Zoom\\Migration_Notice' => __DIR__ . '/../..' . '/src/Tribe/Meetings/Zoom/Migration_Notice.php',
        'Tribe\\Events\\Virtual\\Meetings\\Zoom\\OAuth' => __DIR__ . '/../..' . '/src/Tribe/Meetings/Zoom/OAuth.php',
        'Tribe\\Events\\Virtual\\Meetings\\Zoom\\Password' => __DIR__ . '/../..' . '/src/Tribe/Meetings/Zoom/Password.php',
        'Tribe\\Events\\Virtual\\Meetings\\Zoom\\Settings' => __DIR__ . '/../..' . '/src/Tribe/Meetings/Zoom/Settings.php',
        'Tribe\\Events\\Virtual\\Meetings\\Zoom\\Template_Modifications' => __DIR__ . '/../..' . '/src/Tribe/Meetings/Zoom/Template_Modifications.php',
        'Tribe\\Events\\Virtual\\Meetings\\Zoom\\Url' => __DIR__ . '/../..' . '/src/Tribe/Meetings/Zoom/Url.php',
        'Tribe\\Events\\Virtual\\Meetings\\Zoom\\Users' => __DIR__ . '/../..' . '/src/Tribe/Meetings/Zoom/Users.php',
        'Tribe\\Events\\Virtual\\Meetings\\Zoom\\Webinars' => __DIR__ . '/../..' . '/src/Tribe/Meetings/Zoom/Webinars.php',
        'Tribe\\Events\\Virtual\\Meetings\\Zoom_Provider' => __DIR__ . '/../..' . '/src/Tribe/Meetings/Zoom_Provider.php',
        'Tribe\\Events\\Virtual\\Metabox' => __DIR__ . '/../..' . '/src/Tribe/Metabox.php',
        'Tribe\\Events\\Virtual\\Models\\Event' => __DIR__ . '/../..' . '/src/Tribe/Models/Event.php',
        'Tribe\\Events\\Virtual\\OEmbed' => __DIR__ . '/../..' . '/src/Tribe/OEmbed.php',
        'Tribe\\Events\\Virtual\\ORM\\ORM_Provider' => __DIR__ . '/../..' . '/src/Tribe/ORM/ORM_Provider.php',
        'Tribe\\Events\\Virtual\\PUE' => __DIR__ . '/../..' . '/src/Tribe/PUE.php',
        'Tribe\\Events\\Virtual\\PUE\\Helper' => __DIR__ . '/../..' . '/src/Tribe/PUE/Helper.php',
        'Tribe\\Events\\Virtual\\Plugin' => __DIR__ . '/../..' . '/src/Tribe/Plugin.php',
        'Tribe\\Events\\Virtual\\Plugin_Register' => __DIR__ . '/../..' . '/src/Tribe/Plugin_Register.php',
        'Tribe\\Events\\Virtual\\Repositories\\Event' => __DIR__ . '/../..' . '/src/Tribe/Repositories/Event.php',
        'Tribe\\Events\\Virtual\\Rewrite\\Rewrite_Provider' => __DIR__ . '/../..' . '/src/Tribe/Rewrite/Rewrite_Provider.php',
        'Tribe\\Events\\Virtual\\Template' => __DIR__ . '/../..' . '/src/Tribe/Template.php',
        'Tribe\\Events\\Virtual\\Template_Modifications' => __DIR__ . '/../..' . '/src/Tribe/Template_Modifications.php',
        'Tribe\\Events\\Virtual\\Traits\\With_AJAX' => __DIR__ . '/../..' . '/src/Tribe/Traits/With_AJAX.php',
        'Tribe\\Events\\Virtual\\Traits\\With_Nonce_Routes' => __DIR__ . '/../..' . '/src/Tribe/Traits/With_Nonce_Routes.php',
        'Tribe\\Events\\Virtual\\Updater' => __DIR__ . '/../..' . '/src/Tribe/Updater.php',
        'Tribe\\Events\\Virtual\\Utils' => __DIR__ . '/../..' . '/src/Tribe/Utils.php',
        'Tribe\\Events\\Virtual\\Views\\V2\\Breadcrumbs' => __DIR__ . '/../..' . '/src/Tribe/Views/V2/Breadcrumbs.php',
        'Tribe\\Events\\Virtual\\Views\\V2\\Query' => __DIR__ . '/../..' . '/src/Tribe/Views/V2/Query.php',
        'Tribe\\Events\\Virtual\\Views\\V2\\Repository' => __DIR__ . '/../..' . '/src/Tribe/Views/V2/Repository.php',
        'Tribe\\Events\\Virtual\\Views\\V2\\Title' => __DIR__ . '/../..' . '/src/Tribe/Views/V2/Title.php',
        'Tribe\\Events\\Virtual\\Views\\V2\\Views_Provider' => __DIR__ . '/../..' . '/src/Tribe/Views/V2/Views_Provider.php',
        'Tribe\\Events\\Virtual\\Views\\V2\\Widgets\\Widget' => __DIR__ . '/../..' . '/src/Tribe/Views/V2/Widgets/Widget.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb77f7e7ac0d9e44c5d28cb91079fb3cc::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb77f7e7ac0d9e44c5d28cb91079fb3cc::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitb77f7e7ac0d9e44c5d28cb91079fb3cc::$classMap;

        }, null, ClassLoader::class);
    }
}
