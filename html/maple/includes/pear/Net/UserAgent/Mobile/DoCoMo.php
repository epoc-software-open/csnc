<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Networking
 * @package    Net_UserAgent_Mobile
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2003-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id: DoCoMo.php,v 1.1 2008/05/13 04:23:36 snakajima Exp $
 * @link       http://www.nttdocomo.co.jp/service/imode/make/content/spec/useragent/index.html
 * @since      File available since Release 0.1
 */

require_once 'Net/UserAgent/Mobile/Common.php';
require_once 'Net/UserAgent/Mobile/Display.php';

// {{{ Net_UserAgent_Mobile_DoCoMo

/**
 * NTT DoCoMo implementation
 *
 * Net_UserAgent_Mobile_DoCoMo is a subclass of {@link Net_UserAgent_Mobile_Common},
 * which implements NTT docomo i-mode user agents.
 *
 * SYNOPSIS:
 * <code>
 * require_once 'Net/UserAgent/Mobile.php';
 *
 * $_SERVER['HTTP_USER_AGENT'] = 'DoCoMo/1.0/P502i/c10';
 * $agent = &Net_UserAgent_Mobile::factory();
 *
 * printf("Name: %s\n", $agent->getName()); // 'DoCoMo'
 * printf("Version: %s\n", $agent->getVersion()); // 1.0
 * printf("HTML version: %s\n", $agent->getHTMLVersion()); // 2.0
 * printf("Model: %s\n", $agent->getModel()); // 'P502i'
 * printf("Cache: %dk\n", $agent->getCacheSize()); // 10
 * if ($agent->isFOMA()) {
 *     print "FOMA\n";             // false
 * }
 * printf("Vendor: %s\n", $agent->getVendor()); // 'P'
 * printf("Series: %s\n", $agent->getSeries()); // '502i'
 *
 * // only available with <form utn>
 * // e.g.) 'DoCoMo/1.0/P503i/c10/serNMABH200331';
 * printf("Serial: %s\n", $agent->getSerialNumber()); // 'NMABH200331'
 *
 * // e.g.) 'DoCoMo/2.0 N2001(c10;ser0123456789abcde;icc01234567890123456789)';
 * printf("Serial: %s\n", $agent->getSerialNumber()); // '0123456789abcde'
 * printf("Card ID: %s\n", $agent->getCardID()); // '01234567890123456789'
 *
 * // e.g.) 'DoCoMo/1.0/P502i (Google CHTML Proxy/1.0)'
 * printf("Comment: %s\n", $agent->getComment()); // 'Google CHTML Proxy/1.0'
 *
 * // e.g.) 'DoCoMo/1.0/D505i/c20/TB/W20H10'
 * printf("Status: %s\n", $agent->getStatus()); // 'TB'
 *
 * // only available in eggy/M-stage
 * // e.g.) 'DoCoMo/1.0/eggy/c300/s32/kPHS-K'
 * printf("Bandwidth: %dkbps\n", $agent->getBandwidth()); // 32
 * </code>
 *
 * @category   Networking
 * @package    Net_UserAgent_Mobile
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2003-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: 1.0.0RC1
 * @link       http://www.nttdocomo.co.jp/service/imode/make/content/spec/useragent/index.html
 * @since      Class available since Release 0.1
 */
class Net_UserAgent_Mobile_DoCoMo extends Net_UserAgent_Mobile_Common
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    /**
     * status of the cache (TC, TB, TD, TJ)
     * @var string
     */
    var $_status;

    /**
     * bandwidth like 32 as kilobytes unit
     * @var integer
     */
    var $_bandwidth;

    /**
     * hardware unique serial number
     * @var string
     */
    var $_serialNumber;

    /**
     * whether it's FOMA or not
     * @var boolean
     */
    var $_isFOMA = false;

    /**
     * FOMA Card ID (20 digit alphanumeric)
     * @var string
     */
    var $_cardID;

    /**
     * comment on user agent string like 'Google Proxy'
     * @var string
     */
    var $_comment;

    /**
     * cache size as killobytes unit
     * @var integer
     */
    var $_cacheSize;

    /**
     * width and height of the display
     * @var string
     */
    var $_displayBytes;

    /**
     * The model names which have GPS capability.
     *
     * @var array
     * @since Property available since Release 1.0.0RC1
     */
    var $_gpsModels = array('F884i',
                            'F801i',
                            'F905iBiz',
                            'SO905iCS',
                            'N905iBiz',
                            'N905imyu',
                            'SO905i',
                            'F905i',
                            'P905i',
                            'N905i',
                            'D905i',
                            'SH905i',
                            'P904i',
                            'D904i',
                            'F904i',
                            'N904i',
                            'SH904i',
                            'F883iESS',
                            'F883iES',
                            'F903iBSC',
                            'SO903i',
                            'F903i',
                            'D903i',
                            'N903i',
                            'P903i',
                            'SH903i',
                            'SA800i',
                            'SA702i',
                            'SA700iS',
                            'F505iGPS',
                            'F661i'
                            );

    /**
     * The HTML version map which maps models to HTML versions.
     *
     * @var array
     * @since Property available since Release 1.0.0RC1
     */
    var $_htmlVersionMap = array('[DFNP]501i' => '1.0',
                                 '502i|821i|209i|651|691i|(F|N|P|KO)210i|^F671i$' => '2.0',
                                 '(D210i|SO210i)|503i|211i|SH251i|692i|200[12]|2101V' => '3.0',
                                 '504i|251i|^F671iS$|212i|2051|2102V|661i|2701|672i|SO213i|850i|^NM705i$' => '4.0',
                                 'eggy|P751v' => '3.2',
                                 '505i|252i|900i|506i|880i|253i|P213i|901i|700i|^(SH|P)851i|701i|881i|^SA800i$|600i|^L601i$|^M702i(S|G)$|^L602i$' => '5.0',
                                 '902i|702i|851i|882i|^N601i$|^D800iDS$|^P703imyu$|^P704imyu$|^L704i$|^L705iX?$|^F883i$' => '6.0',
                                 '903i|703i|904i|704i|883i|801i|^(F|D|SH)705i' => '7.0',
                                 '905i|705i|884i' => '7.1'
                                 );

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ isDoCoMo()

    /**
     * returns true
     *
     * @return boolean
     */
    function isDoCoMo()
    {
        return true;
    }

    // }}}
    // {{{ parse()

    /**
     * Parses HTTP_USER_AGENT string.
     *
     * @param string $userAgent User-Agent string
     * @throws Net_UserAgent_Mobile_Error
     */
    function parse($userAgent)
    {
        @list($main, $foma_or_comment) = explode(' ', $userAgent, 2);

        if ($foma_or_comment
            && preg_match('/^\((.*)\)$/', $foma_or_comment, $matches)
            ) {

            // DoCoMo/1.0/P209is (Google CHTML Proxy/1.0)
            $this->_comment = $matches[1];
            $result = $this->_parseMain($main);
        } elseif ($foma_or_comment) {

            // DoCoMo/2.0 N2001(c10;ser0123456789abcde;icc01234567890123456789)
            $this->_isFOMA = true;
            list($this->name, $this->version) = explode('/', $main);
            $result = $this->_parseFOMA($foma_or_comment);
        } else {

            // DoCoMo/1.0/R692i/c10
            $result = $this->_parseMain($main);
        }

        if (Net_UserAgent_Mobile::isError($result)) {
            return $result;
        }
    }

    // }}}
    // {{{ makeDisplay()

    /**
     * create a new {@link Net_UserAgent_Mobile_Display} class instance
     *
     * @return Net_UserAgent_Mobile_Display
     */
    function makeDisplay()
    {
        include_once 'Net/UserAgent/Mobile/DoCoMo/ScreenInfo.php';
        $screenInfo = &Net_UserAgent_Mobile_DoCoMo_ScreenInfo::singleton();
        $display = $screenInfo->get($this->getModel());
        if (!is_null($this->_displayBytes)) {
            list($widthBytes, $heightBytes) = explode('*', $this->_displayBytes);
            $display['width_bytes']  = $widthBytes;
            $display['height_bytes'] = $heightBytes;
        }

        return new Net_UserAgent_Mobile_Display($display);
    }

    // }}}
    // {{{ getHTMLVersion()

    /**
     * returns supported HTML version like '3.0'. retuns null if unknown.
     *
     * @return string
     */
    function getHTMLVersion()
    {
        foreach ($this->_htmlVersionMap as $key => $value) {
            if (preg_match("/$key/", $this->_rawModel)) {
                return $value;
            }
        }
    }

    // }}}
    // {{{ getCacheSize()

    /**
     * returns cache size as kilobytes unit. returns 5 if unknown.
     *
     * @return integer
     */
    function getCacheSize()
    {
        if ($this->_cacheSize) {
            return $this->_cacheSize;
        }

        return 5;
    }

    // }}}
    // {{{ getSeries()

    /**
     * returns series name like '502i'. returns null if unknown.
     *
     * @return string
     */
    function getSeries()
    {
        if (preg_match('/(\d{4})/', $this->_rawModel)) {
            return 'FOMA';
        }

        if (preg_match('/(\d{3}i)/', $this->_rawModel, $matches)) {
            return $matches[1];
        }

        if ($this->_rawModel == 'P651ps') {
            return '651';
        }
    }

    // }}}
    // {{{ getVendor()

    /**
     * returns vender code like 'SO' for Sony. returns null if unknown.
     *
     * @return string
     */
    function getVendor()
    {
        if (preg_match('/([A-Z]+)\d/', $this->_rawModel, $matches)) {
            return $matches[1];
        }
    }

    // }}}
    // {{{ getStatus()

    /**
     * returns status like "TB", "TC", "TD" or "TJ", which means:
     * 
     * TB | Browsers
     * TC | Browsers with image off (only Available in HTML 5.0)
     * TD | Fetching JAR
     * TJ | i-Appli
     *
     * @return string
     */
    function getStatus()
    {
        return $this->_status;
    }

    // }}}
    // {{{ getBandwidth()

    /**
     * returns bandwidth like 32 as killobytes unit. Only vailable in eggy,
     * returns null otherwise.
     *
     * @return integer
     */
    function getBandwidth()
    {
        return $this->_bandwidth;
    }

    // }}}
    // {{{ getSerialNumber()

    /**
     * returns hardware unique serial number (15 digit in FOMA, 11 digit
     * otherwise alphanumeric). Only available with form utn attribute.
     * returns null otherwise.
     *
     * @return string
     */
    function getSerialNumber()
    {
        return $this->_serialNumber;
    }

    // }}}
    // {{{ isFOMA()

    /**
     * retuns whether it's FOMA or not
     *
     * @return boolean
     */
    function isFOMA()
    {
        return $this->_isFOMA;
    }

    // }}}
    // {{{ getComment()

    /**
     * returns comment on user agent string like 'Google Proxy'. returns null
     * otherwise.
     *
     * @return string
     */
    function getComment()
    {
        return $this->_comment;
    }

    // }}}
    // {{{ getCardID()

    /**
     * returns FOMA Card ID (20 digit alphanumeric). Only available in FOMA
     * with <form utn> attribute. returns null otherwise.
     *
     * @return string
     */ 
    function getCardID()
    {
        return $this->_cardID;
    }

    // }}}
    // {{{ isGPS()

    /**
     * Returns whether a user agent is a GPS model or not.
     *
     * @return boolean
     */ 
    function isGPS()
    {
        return in_array($this->_rawModel, $this->_gpsModels);
    }

    // }}}
    // {{{ getCarrierShortName()

    /**
     * returns the short name of the carrier
     *
     * @return string
     */
    function getCarrierShortName()
    {
        return 'I';
    }

    // }}}
    // {{{ getCarrierLongName()

    /**
     * returns the long name of the carrier
     *
     * @return string
     */
    function getCarrierLongName()
    {
        return 'DoCoMo';
    }

    // }}}
    // {{{ getUID()

    /**
     * Gets the UID of a subscriber.
     *
     * @return string
     * @since Method available since Release 1.0.0RC1
     */
    function getUID()
    {
        if (array_key_exists('HTTP_X_DCMGUID', $_SERVER)) {
            return $_SERVER['HTTP_X_DCMGUID'];
        }
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _parseMain()

    /**
     * parse main part of HTTP_USER_AGENT string (not FOMA)
     *
     * @param string $main main part of HTTP_USER_AGENT string
     * @throws Net_UserAgent_Mobile_Error
     */ 
    function _parseMain($main)
    {
        @list($this->name, $this->version, $this->_rawModel, $cache, $rest) =
            explode('/', $main, 5);
        if ($this->_rawModel == 'SH505i2') {
            $this->_model = 'SH505i';
        }

        if ($cache) {
            if (!preg_match('/^c(\d+)$/', $cache, $matches)) {
                return $this->noMatch();
            }
            $this->_cacheSize = (integer)$matches[1];
        }

        if ($rest) {
            $rest = explode('/', $rest);
            foreach ($rest as $value) {
                if (preg_match('/^ser(\w{11})$/', $value, $matches)) {
                    $this->_serialNumber = $matches[1];
                    continue;
                }
                if (preg_match('/^(T[CDBJ])$/', $value, $matches)) {
                    $this->_status = $matches[1];
                    continue;
                }
                if (preg_match('/^s(\d+)$/', $value, $matches)) {
                    $this->_bandwidth = (integer)$matches[1];
                    continue;
                }
                if (preg_match('/^W(\d+)H(\d+)$/', $value, $matches)) {
                    $this->_displayBytes = "{$matches[1]}*{$matches[2]}";
                    continue;
                }
            }
        }
    }

    // }}}
    // {{{ _parseFOMA()

    /**
     * parse main part of HTTP_USER_AGENT string (FOMA)
     *
     * @param string $foma main part of HTTP_USER_AGENT string
     * @throws Net_UserAgent_Mobile_Error
     */ 
    function _parseFOMA($foma)
    {
        if (!preg_match('/^([^(\s]+)/', $foma, $matches)) {
            return $this->noMatch();
        }

        $this->_rawModel = $matches[1];
        if ($this->_rawModel == 'MST_v_SH2101V') {
            $this->_model = 'SH2101V';
        }

        if (preg_match('/^[^(\s]+\s?\(([^)]+)\)(?:\(([^)]+)\))?$/', $foma, $matches)) {
            if (preg_match('/^compatible/', $matches[1])) { // The user-agent is DoCoMo compatible.
                $this->_comment = $matches[1];
                return;
            }

            if (count($matches) == 3) {
                if (preg_match('/^compatible/', $matches[2])) { // The user-agent is DoCoMo compatible.
                    $this->_comment = $matches[2];
                }
            }

            $rest = explode(';', $matches[1]);
            foreach ($rest as $value) {
                if (preg_match('/^c(\d+)$/', $value, $matches)) {
                    $this->_cacheSize = (integer)$matches[1];
                    continue;
                }
                if (preg_match('/^ser(\w{15})$/', $value, $matches)) {
                    $this->_serialNumber = $matches[1];
                    continue;
                }
                if (preg_match('/^(T[CDBJ])$/', $value, $matches)) {
                    $this->_status = $matches[1];
                    continue;
                }
                if (preg_match('/^icc(\w{20})?$/', $value, $matches)) {
                    if (count($matches) == 2) {
                        $this->_cardID = $matches[1];
                    }
                    continue;
                }
                if (preg_match('/^W(\d+)H(\d+)$/', $value, $matches)) {
                    $this->_displayBytes = "{$matches[1]}*{$matches[2]}";
                    continue;
                }
                return $this->noMatch();
            }
        }
    }

    /**#@-*/

    // }}}
}

// }}}

/*
 * Local Variables:
 * mode: php
 * coding: iso-8859-1
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * indent-tabs-mode: nil
 * End:
 */
