<?php


namespace MoOauthClient\Free;

require_once "\x76\x74\x2d\143\x6f\156\163\164\163\x2e\x70\150\160";
class MOCVisualTour
{
    protected $nonce;
    protected $nonce_key;
    protected $tour_ajax_action;
    public function __construct()
    {
        $this->nonce = "\155\x6f\x5f\x61\144\x6d\151\x6e\137\141\x63\164\x69\157\x6e\x73";
        $this->nonce_key = "\x73\x65\x63\165\162\151\x74\x79";
        $this->tour_ajax_action = "\x6d\x69\156\151\x6f\162\x61\x6e\147\145\x2d\x74\x6f\165\x72\55\164\141\x6b\145\156";
        add_action("\x61\144\x6d\x69\156\x5f\x65\156\161\x75\145\165\145\x5f\x73\x63\x72\x69\x70\164\x73", array($this, "\x65\156\x71\x75\145\165\145\x5f\166\151\163\165\141\x6c\x5f\164\157\165\162\137\163\x63\x72\151\x70\x74"));
        add_action("\x77\x70\x5f\x61\x6a\141\170\x5f{$this->tour_ajax_action}", array($this, "\x75\x70\x64\141\164\x65\x5f\x74\157\x75\x72\137\164\141\153\x65\156"));
        add_action("\x77\160\137\x61\152\x61\x78\x5f\x6e\157\160\162\151\x76\x5f{$this->tour_ajax_action}", array($this, "\x75\160\144\x61\164\145\x5f\x74\157\165\162\x5f\x74\x61\x6b\x65\156"));
    }
    public function update_tour_taken()
    {
        global $NQ;
        $this->validate_ajax_request();
        $NQ->mo_oauth_client_update_option("\x74\157\x75\x72\124\x61\x6b\x65\156\137" . $_POST["\x70\x61\147\x65\x49\x44"], $_POST["\x64\x6f\156\145\124\157\x75\162"]);
        $NQ->mo_oauth_client_update_option("\x6d\157\x63\137\164\157\165\162\124\x61\153\145\x6e\137\x66\x69\162\163\164", true);
        die;
    }
    private function validate_ajax_request()
    {
        if (check_ajax_referer($this->nonce, $this->nonce_key)) {
            goto NC;
        }
        wp_send_json(array("\x6d\145\163\x73\141\x67\x65" => "\x49\156\166\141\154\x69\x64\40\x4f\x70\145\162\141\164\x69\157\156\x2e\x20\x50\154\x65\x61\163\145\x20\164\162\x79\x20\141\x67\141\x69\156\56", "\162\x65\x73\165\154\x74" => "\x65\162\162\x6f\x72"));
        die;
        NC:
    }
    public function enqueue_visual_tour_script()
    {
        global $NQ;
        wp_register_script("\x74\157\x75\162\137\x73\x63\x72\151\x70\x74", TOUR_RES_JS . "\166\151\x73\165\141\154\x54\x6f\165\x72\56\152\x73", array("\x6a\x71\x75\145\x72\x79"));
        $OT = isset($_GET["\x74\141\142"]) && '' !== $_GET["\164\141\142"] ? $_GET["\x74\141\142"] : '';
        wp_localize_script("\x74\157\x75\x72\x5f\163\143\x72\x69\x70\x74", "\155\157\x54\x6f\165\x72", array("\163\x69\x74\x65\125\122\x4c" => admin_url("\141\x64\x6d\151\156\55\x61\x6a\x61\170\x2e\x70\150\x70"), "\164\x6e\x6f\156\x63\145" => \wp_create_nonce($this->nonce), "\x70\x61\x67\145\111\104" => $OT, "\164\x6f\x75\162\x44\x61\164\141" => $this->get_tour_data($OT), "\164\157\x75\162\x54\141\153\145\x6e" => $NQ->mo_oauth_client_get_option("\x74\157\x75\162\x54\141\x6b\145\156\x5f" . $OT), "\141\x6a\x61\x78\101\143\164\x69\x6f\x6e" => $this->tour_ajax_action, "\x6e\157\156\x63\145\x4b\x65\x79" => \wp_create_nonce($this->nonce_key)));
        wp_enqueue_script("\164\157\165\x72\x5f\163\x63\162\151\160\x74");
        wp_enqueue_style("\x6d\x6f\143\x5f\166\x69\x73\x75\x61\x6c\x5f\164\157\165\162\137\x73\x74\171\154\x65", TOUR_RES_CSS . "\166\x69\x73\x75\x61\154\124\157\x75\x72\56\143\x73\163");
    }
    public function tour_template($us, $X8, $gV, $NF, $kr, $Qr, $wF)
    {
        $O3 = array("\x73\155\141\154\x6c", "\155\145\144\x69\x75\155", "\142\x69\x67");
        return array("\164\x61\x72\x67\145\x74\x45" => $us, "\160\157\x69\x6e\x74\x54\x6f\123\x69\x64\x65" => $X8, "\164\x69\164\x6c\x65\x48\x54\115\x4c" => $gV, "\x63\x6f\156\164\145\156\x74\x48\124\x4d\114" => $NF, "\142\165\164\x74\157\x6e\124\145\170\x74" => $kr, "\x69\x6d\147" => $Qr ? TOUR_RES_IMG . $Qr : $Qr, "\x63\x61\x72\144\123\151\172\x65" => $O3[$wF]);
    }
    private function get_tour_data($OT = '')
    {
        global $NQ;
        $or = array();
        if (boolval($NQ->mo_oauth_client_get_option("\155\157\x63\x5f\164\157\165\162\124\x61\153\x65\156\137\x66\x69\x72\x73\x74"))) {
            goto Nz;
        }
        $or = array($this->tour_template('', '', "\74\x68\61\x3e\x57\145\154\x63\x6f\x6d\x65\x21\74\x2f\150\61\x3e", "\106\141\x73\x74\145\156\x20\171\157\165\162\x20\x73\x65\141\x74\x20\x62\145\154\x74\163\40\146\x6f\162\x20\141\40\161\x75\x69\143\x6b\x20\162\151\x64\145\x2e", "\114\145\x74\47\163\x20\x47\157\x21", "\x73\164\141\x72\164\x54\157\165\x72\56\x73\166\x67", 2));
        $or = array_merge($or, $this->get_tab_pointers());
        Nz:
        if (!("\143\157\156\x66\151\x67" === $OT)) {
            goto jd;
        }
        if (!(isset($_GET["\141\x63\164\x69\x6f\x6e"]) && "\x75\x70\x64\141\164\145" === $_GET["\x61\x63\164\151\x6f\156"])) {
            goto Yn;
        }
        $or = array_merge($or, $this->get_updateui_pointers());
        Yn:
        $RL = $NQ->mo_oauth_client_get_option("\155\x6f\x5f\157\141\165\164\150\137\x61\x70\x70\x73\137\x6c\x69\x73\x74") ? $NQ->mo_oauth_client_get_option("\155\x6f\137\x6f\141\x75\164\150\137\141\160\x70\x73\x5f\x6c\x69\x73\164") : false;
        if ($RL && is_array($RL) && 0 < count($RL) && !isset($_GET["\x61\160\x70\111\x64"])) {
            goto H7;
        }
        if (!isset($_GET["\x61\x70\x70\x49\x64"])) {
            goto TT;
        }
        goto l5;
        H7:
        $or = array_merge($or, $this->get_applist_pointers());
        goto l5;
        TT:
        $or = array_merge($or, $this->get_defaultapps_pointers());
        l5:
        if (!(isset($_GET["\141\x70\160\111\x64"]) && '' !== $_GET["\141\160\x70\x49\x64"])) {
            goto Ac;
        }
        $or = array_merge($or, $this->get_addapp_pointers());
        Ac:
        jd:
        if (!("\x73\x69\147\156\x69\156\x73\145\x74\164\151\x6e\x67\163" === $OT)) {
            goto JQ;
        }
        $or = array_merge($or, $this->get_signinsettings_pointers());
        JQ:
        return $or;
    }
    private function get_tab_pointers()
    {
        return array($this->tour_template("\x6d\157\x5f\x73\165\x70\160\157\x72\164\137\154\141\x79\157\x75\164", "\162\x69\x67\150\164", "\74\x68\x31\x3e\x57\145\x20\x61\162\145\x20\x68\x65\x72\145\x21\41\74\57\150\x31\x3e", "\107\145\x74\x20\151\x6e\40\x74\x6f\x75\143\x68\x20\x77\151\164\x68\40\x75\x73\40\141\x6e\x64\40\x77\145\40\x77\x69\154\x6c\40\x68\145\154\x70\40\171\x6f\x75\40\163\x65\x74\x75\160\x20\x74\150\145\x20\160\x6c\165\147\x69\156\x20\151\156\40\x6e\x6f\x20\164\x69\x6d\x65\x2e", "\116\x65\x78\164", "\150\145\154\x70\56\x73\x76\147", 2), $this->tour_template("\x74\141\x62\55\143\x6f\156\x66\151\x67", "\165\160", "\74\x68\x31\76\x43\x6f\156\146\151\x67\x75\162\141\164\x69\x6f\x6e\40\124\141\142\74\x2f\150\x31\76", "\x59\157\165\x20\x63\x61\156\40\143\x68\157\x6f\x73\145\40\x61\156\144\40\143\157\156\x66\151\147\165\162\145\x20\x61\156\171\x20\117\x41\x75\x74\150\57\117\160\145\x6e\111\104\40\141\x70\160\x6c\x69\x63\x61\x74\151\x6f\156\56", "\116\145\x78\164", "\143\150\157\157\163\145\56\163\x76\x67", 2), $this->tour_template("\x74\x61\x62\55\x63\165\163\x74\157\x6d\151\x7a\141\164\x69\157\x6e", "\165\x70", "\x3c\x68\61\x3e\127\151\x64\147\145\x74\40\x43\x75\x73\x74\157\x6d\151\x7a\141\164\x69\157\x6e\x20\124\141\x62\74\x2f\150\61\76", "\131\157\x75\x20\143\141\156\40\143\x75\163\164\157\x6d\151\x7a\x65\40\x79\157\x75\x72\x20\154\x6f\x67\151\x6e\40\167\x69\144\x67\145\x74\40\x6f\x72\40\163\150\x6f\x72\x74\x63\x6f\144\x65\40\x77\151\x64\x67\x65\x74\40\164\157\x20\x79\x6f\x75\162\40\x6c\151\153\x69\156\x67\40\x77\151\164\x68\40\103\123\x53\x20\150\145\162\x65\x21", "\116\145\170\164", "\x63\150\x6f\x6f\x73\x65\56\163\x76\x67", 2), $this->tour_template("\x74\x61\142\x2d\163\x69\x67\x6e\x69\x6e\163\x65\x74\x74\151\156\147\x73", "\x75\x70", "\x3c\x68\x31\76\123\151\147\x6e\40\111\156\40\123\145\164\164\151\x6e\147\163\74\57\150\61\76", "\131\x6f\165\x20\143\141\156\x20\x66\151\x6e\144\x20\166\x61\162\x69\157\x75\x73\x20\123\x53\117\40\162\x65\x6c\141\x74\x65\x64\40\x63\x6f\156\146\x69\147\165\162\141\x74\x69\x6f\156\x73\x20\163\165\143\x68\x20\x61\163\x20\x73\x68\157\x72\x74\x63\x6f\x64\145\163\x20\x61\x6e\x64\x20\x55\x73\145\162\x20\122\x65\147\x69\x73\x74\162\141\164\x69\157\x6e\40\150\145\162\145\x21", "\116\x65\170\x74", "\160\x72\157\146\x69\154\145\56\x73\166\147", 2), $this->tour_template("\x74\141\142\x2d\x72\145\161\x75\145\x73\x74\x64\145\x6d\x6f", "\165\160", "\x3c\150\61\x3e\122\145\x71\165\x65\163\x74\40\106\157\x72\40\x44\145\x6d\x6f\x3c\x2f\x68\61\76", "\x41\x72\x65\40\x79\157\x75\40\154\x6f\x6f\x6b\x69\x6e\147\x20\x66\157\x72\x20\x70\x72\x65\155\x69\165\155\40\x66\x65\141\164\x75\162\145\x73\77\x20\x4e\157\x77\x2c\x20\171\157\x75\x20\143\141\x6e\40\x73\145\156\144\x20\x61\x20\162\145\161\165\x65\x73\x74\40\x74\x6f\x20\x73\x65\164\x75\x70\40\x61\x20\x64\x65\155\x6f\40\x6f\146\x20\164\150\x65\x20\x70\x72\x65\155\151\165\x6d\40\x76\145\x72\x73\x69\x6f\x6e\x20\x79\157\165\40\141\x72\145\x20\x69\156\164\x65\162\x65\x73\x74\x65\x64\x20\x69\x6e\x20\x61\x6e\x64\x20\157\x75\162\40\x74\x65\x61\x6d\x20\x77\151\154\x6c\x20\163\145\x74\40\x69\x74\x20\x75\160\x20\x66\157\162\40\171\x6f\165\x21", "\116\x65\170\164", "\x70\162\145\166\x69\x65\167\x2e\163\x76\x67", 2), $this->tour_template("\x6c\x69\143\145\x6e\163\151\x6e\x67\x5f\x62\165\164\164\x6f\156\x5f\151\x64", "\x75\160", "\74\x68\61\76\x4c\x69\143\x65\x6e\163\x69\156\147\x20\120\154\x61\x6e\x73\74\x2f\x68\61\x3e", "\x59\157\165\40\143\141\156\x20\x63\150\145\143\153\x20\141\154\154\x20\164\150\145\x20\154\x69\x63\x65\156\x73\151\156\147\40\x70\154\x61\x6e\163\x20\141\156\x64\x20\164\x68\145\x20\x66\145\141\164\165\162\x65\x73\x20\x61\163\40\x77\145\154\x6c\40\141\163\40\157\x70\164\151\x6f\156\163\x20\x74\x68\145\x79\40\157\x66\146\x65\x72\x2c\x20\x68\x65\x72\145\56", "\x4e\x65\170\164", "\165\160\x67\162\141\x64\x65\56\x73\166\147", 2), $this->tour_template("\146\x61\161\137\x62\165\164\164\x6f\156\x5f\x69\144", "\x75\160", "\x3c\150\x31\76\106\141\x63\x69\156\x67\x20\x61\40\x70\x72\157\x62\x6c\x65\155\77\74\57\150\x31\76", "\x59\x6f\165\40\143\x61\156\x20\x63\150\145\x63\153\x20\x46\101\121\x73\x2e\40\115\157\x73\164\40\161\165\145\163\164\x69\x6f\156\x73\x20\x63\141\x6e\40\x62\145\40\x73\157\x6c\x76\145\x64\x20\x62\171\40\162\145\x61\144\x69\156\x67\x20\164\x68\x72\157\x75\x67\x68\x20\x74\x68\145\40\106\101\121\163\x2e\56", "\x4e\145\x78\x74", "\146\x61\x71\x2e\163\x76\147", 2), $this->tour_template("\141\143\x63\137\163\145\x74\x75\160\137\142\165\164\164\x6f\x6e\137\x69\144", "\x75\160", "\x3c\x68\x31\x3e\x49\40\x77\141\156\164\x20\x74\x6f\40\165\x70\147\162\141\x64\145\41\74\x2f\150\61\x3e", "\x59\157\x75\40\x64\157\x20\156\x6f\164\x20\156\x65\x65\144\x20\x74\x6f\x20\x73\x65\164\165\160\40\171\157\165\x72\40\x61\143\143\157\165\x6e\x74\40\x74\x6f\40\165\163\145\x20\x74\x68\x65\x20\160\x6c\165\x67\151\x6e\x2e\x20\111\146\40\171\157\x75\x20\167\x61\156\164\40\x74\x6f\40\165\160\x67\x72\141\x64\x65\x2c\x20\171\x6f\165\40\x77\x69\154\x6c\40\156\145\145\x64\x20\141\x20\155\x69\156\x69\117\x72\141\156\x67\x65\40\141\x63\143\157\x75\156\x74\56", "\x4e\x65\x78\x74", "\x70\157\x70\125\160\x2e\163\x76\147", 2), $this->tour_template("\162\x65\163\x74\141\x72\164\x5f\x74\157\x75\x72\x5f\142\165\x74\164\157\156", "\162\x69\x67\x68\164", "\74\150\x31\x3e\x52\x65\163\164\141\162\x74\x20\124\157\x75\162\74\57\150\61\76", "\x49\146\40\x79\157\165\x20\x6e\145\x65\144\x20\x74\157\40\x72\x65\x76\151\163\151\164\x20\x74\x68\145\40\164\x6f\x75\162\54\x20\171\x6f\x75\40\143\141\x6e\40\165\x73\145\x20\x74\150\151\163\x20\142\165\164\x74\157\156\40\x74\157\x20\162\145\160\154\141\171\x20\x69\x74\40\146\x6f\162\x20\x74\x68\145\40\143\165\x72\162\145\x6e\164\x20\164\x61\x62\x21", "\116\145\x78\x74", "\162\x65\x70\154\141\171\x2e\x73\166\x67", 2));
    }
    private function get_updateui_pointers()
    {
        return array($this->tour_template("\x6d\157\137\157\141\165\164\150\x5f\164\145\x73\x74\x5f\143\157\156\x66\x69\147\x75\x72\141\x74\x69\157\156", "\x6c\145\x66\x74", "\x3c\150\61\76\x54\x65\163\164\x20\x79\x6f\165\x72\x20\x63\x6f\x6e\146\151\x67\x75\162\141\x74\151\x6f\156\74\x2f\x68\x31\x3e", "\x43\154\151\x63\x6b\40\150\x65\162\x65\x20\164\157\40\163\x65\x65\40\164\150\x65\40\x6c\151\x73\x74\40\157\146\x20\x61\x74\164\162\x69\142\x75\x74\145\163\40\160\x72\157\166\151\x64\145\144\x20\142\171\40\171\x6f\x75\x72\x20\117\101\165\164\x68\x20\120\x72\157\166\x69\144\145\x72\56\x20\x49\x66\40\171\x6f\165\40\141\x72\145\40\147\x65\x74\x74\151\x6e\x67\x20\x61\156\171\x20\x65\162\162\157\162\x2c\40\x70\154\145\x61\x73\145\40\162\x65\x66\145\162\x20\x74\150\145\40\x46\101\121\40\164\141\x62\56", "\116\x65\170\x74", "\x70\x72\x65\166\151\145\x77\x2e\x73\166\x67", 2), $this->tour_template("\141\164\164\x72\x6d\x61\160\x70\151\156\147", "\154\x65\146\164", "\x3c\150\61\76\115\x61\x70\160\151\156\147\x20\x41\x74\164\162\151\142\x75\164\145\x73\x3c\x2f\x68\x31\76", "\105\x6e\x74\x65\162\x20\x74\150\145\40\141\x70\160\x72\157\160\162\x69\x61\x74\x65\x20\x76\x61\x6c\165\145\163\50\x61\164\164\x72\151\142\165\164\x65\x20\x6e\x61\x6d\145\163\51\40\146\x72\x6f\155\40\x74\x68\145\40\x54\145\163\164\x20\103\157\156\146\x69\147\165\x72\x61\164\x69\x6f\156\40\x74\141\142\154\x65\56", "\x4e\145\x78\164", "\x70\162\145\x76\151\x65\167\56\x73\166\147", 2), $this->tour_template("\x72\x6f\154\145\155\x61\160\x70\x69\x6e\147", "\x6c\x65\x66\164", "\74\150\x31\x3e\x4d\x61\x70\160\151\x6e\147\40\122\x6f\154\145\163\x3c\57\x68\x31\76", "\105\156\x74\x65\162\x20\164\x68\145\40\x72\x6f\154\145\40\x76\x61\x6c\x75\x65\163\x20\146\162\157\155\40\x79\x6f\x75\x72\x20\x4f\101\165\x74\x68\57\117\x70\x65\156\111\x44\x20\160\162\x6f\166\x69\144\x65\162\x20\x61\x6e\x64\40\164\150\145\x6e\x20\x73\x65\154\x65\x63\164\40\x74\x68\145\40\127\157\162\x64\x50\162\145\163\x73\x20\x52\x6f\x6c\x65\40\164\x68\141\x74\x20\171\157\x75\40\x6e\145\x65\x64\40\164\x6f\x20\141\x73\163\151\147\x6e\40\164\x68\x61\x74\x20\162\157\154\x65\56", "\146\x61\154\163\x65", "\160\x72\145\166\x69\x65\x77\x2e\x73\x76\147", 2));
    }
    private function get_signinsettings_pointers()
    {
        return array($this->tour_template("\167\x69\x64\x2d\163\x68\157\x72\164\x63\157\x64\145", "\154\x65\x66\164", "\x3c\x68\x31\76\x53\x69\147\x6e\x20\x49\x6e\40\117\160\164\151\157\x6e\163\x3c\57\x68\x31\76", "\x59\157\x75\x20\x63\x61\x6e\40\144\151\163\160\154\141\x79\40\171\157\165\162\x20\154\x6f\147\x69\156\x20\x62\165\x74\x74\157\x6e\40\165\163\151\x6e\x67\x20\x74\x68\x65\163\145\40\155\145\x74\150\x6f\144\x73\56", "\116\x65\170\164", "\x70\162\145\166\x69\x65\x77\x2e\x73\x76\x67", 2), $this->tour_template("\141\x64\x76\x61\156\x63\145\x64\x5f\163\145\164\x74\151\x6e\x67\x73\x5f\163\x73\x6f", "\x6c\145\146\x74", "\74\150\61\76\101\144\x76\x61\x6e\x63\x65\144\40\x53\x65\x74\x74\x69\x6e\x67\x73\x3c\57\x68\x31\76", "\x59\x6f\x75\x20\x63\x61\x6e\40\x63\x6f\x6e\x66\151\x67\x75\162\145\x20\166\x61\162\x69\157\x75\163\40\157\160\x74\x69\157\156\163\40\154\x69\x6b\145\40\103\x61\x6c\154\142\141\x63\x6b\x20\125\122\114\54\x20\104\157\155\x61\151\x6e\40\x52\x65\163\x74\162\151\x63\164\151\157\156\54\40\x65\164\x63\x2e\40\x68\x65\162\x65\x2e", "\x66\x61\x6c\163\145", "\x70\162\x65\x76\x69\145\167\x2e\x73\166\x67", 2));
    }
    private function get_applist_pointers()
    {
        return array($this->tour_template("\155\157\x5f\x6f\141\165\164\150\137\x61\x70\x70\137\154\151\x73\164", "\154\x65\146\x74", "\74\x68\x31\x3e\101\160\x70\40\x4c\x69\163\x74\x3c\x2f\150\x31\x3e", "\x43\154\x69\143\x6b\x20\150\x65\162\145\x20\x74\x6f\x20\x55\160\144\x61\x74\x65\40\x6f\x72\40\x44\145\x6c\x65\x74\x65\x20\x74\150\145\40\141\x70\160\x6c\x69\x63\141\x74\151\157\x6e\56", "\146\141\x6c\163\145", "\x70\x72\x65\x76\x69\145\167\56\x73\x76\147", 2));
    }
    private function get_defaultapps_pointers()
    {
        return array($this->tour_template("\x6d\x6f\137\157\141\165\x74\150\x5f\x63\x6c\x69\x65\156\164\x5f\x64\x65\146\x61\x75\154\x74\137\x61\x70\160\x73\x5f\143\157\x6e\164\141\151\x6e\x65\162", "\154\145\x66\164", "\74\x68\x31\x3e\x53\x65\154\x65\x63\164\x20\117\101\x75\x74\150\40\x50\x72\x6f\x76\x69\144\x65\x72\74\57\x68\61\76", "\103\x68\x6f\157\163\x65\40\x79\157\x75\x72\x20\117\101\165\x74\150\x20\120\x72\157\x76\x69\x64\x65\x72\40\146\x72\157\155\x20\x74\x68\x65\x20\154\x69\163\164\x20\157\x66\40\x4f\x41\165\164\150\x20\120\162\157\166\151\x64\x65\162\x73", "\x66\141\154\163\x65", "\x70\x72\145\x76\x69\145\167\x2e\x73\x76\147", 2));
    }
    private function get_addapp_pointers()
    {
        return array($this->tour_template("\x6d\157\137\x6f\141\165\164\150\x5f\x63\x6f\x6e\x66\x69\147\137\x67\165\x69\x64\145", "\x6c\x65\x66\164", "\x3c\x68\61\76\103\x6f\156\x66\x69\147\x75\x72\x65\x20\131\x6f\x75\162\x20\x41\x70\x70\x3c\x2f\150\x31\76", "\x4e\145\145\144\x20\x68\x65\x6c\160\40\x77\151\x74\150\40\x63\157\156\146\x69\x67\x75\x72\x61\164\151\x6f\156\77\x20\103\x6c\151\143\x6b\x20\157\156\x20\110\157\x77\40\x74\157\x20\103\x6f\x6e\146\x69\x67\x75\162\145\77", "\x66\141\x6c\163\145", '', 1));
    }
}
