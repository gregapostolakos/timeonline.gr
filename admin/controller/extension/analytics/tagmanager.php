<?php
class ControllerExtensionAnalyticsTagManager extends Controller
{
const EXT_ID = "30750";
const SETTING_PREFIX = "analytics_";
const DMT_CODE = "dmt";
private $token;
private $catalog_url;
private $error = array();
public function __construct($registry)
{
goto dfe6f;
b4ef3:
$this->catalog = isset($this->request->server["HTTPS"]) ? HTTPS_CATALOG : HTTP_CATALOG;
goto F2f10;
F2f10:
$this->token = isset($this->session->data["user_token"]) ? "user_token=" . $this->session->data["user_token"] : "token=" . $this->session->data["token"];
goto A6cc3;
dfe6f:
parent::__construct($registry);
goto b4ef3;
A6cc3:
}
public function index()
{
goto A1906;
e8750:
/*curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);*/
goto B1ffe;
Bcd83:
$curl_result = '';
goto a5907;
a01de:
Ab041:
goto b7287;
E63ef:
$this->response->setOutput($this->load->view("extension/analytics/dmt_licence.tpl", $data));
goto B9721;
Ecb4a:
b0eb7:
goto bcec0;
C7074:
$this->model_setting_setting->editSetting("module_mod_google", $post_data, $store_id_save);
goto Ca61f;
fe4ca:
$prefix = '';
goto E47f2;
f281d:
$data["breadcrumbs"][] = array("text" => $this->language->get("text_home"), "href" => $this->url->link("common/dashboard", $this->token, true));
goto fccac;
Ba6dc:
$tpl_data["transactions"] = $this->model_extension_module_dmt->getTransactions($url_params, $store_id_param, $tpl_data["tagmanager"]);
goto Eba66;
cdc60:
$data["your_status"] = "Active";
goto Fc391;
A6891:
if ($oc_version == "\62\56\60") {
goto D9a68;
}
goto ca7cc;
B29d4:
$tpl_data["tagmanager"]["route_checkout"] = $tm_config["alt_checkout"];
goto A31f0;
C2906:
Da9b1:
goto F2675;
b0918:
$filesize = $filesize / 1024;
goto Ad61f;
e925d:
$data["download_url"] = false;
goto ad1e9;
af012:
$tpl_data["catalog"] = $this->catalog;
goto A006c;
e23ab:
$prefix = '';
goto aa565;
B4d6d:
if (!($dmt_config["licence"] != $licence_hash || $dmt_config["domain"] != $domain)) {
goto A16dc;
}
goto C74dc;
aa565:
a609d:
goto Fb411;
ae8dd:
goto cb519;
goto Aa644;
D0152:
A884a:
goto E7169;
ef9b6:
ebeaf:
goto De869;
c3bbc:
$tpl_data["tagmanager"]["vs"] = base64_encode($version_string);
goto D944c;
f06d5:
$tpl_route = "analytics/tagmanager";
goto F5213;
Aa644:
Bf90a:
goto B36ee;
c6238:
$dmt_config = array("order_id" => base64_decode($licence_info["order_id"]), "licence" => $licence_info["licence"], "domain" => base64_decode($licence_info["domain"]), "email" => base64_decode($licence_info["email"]), "ep" => base64_decode($licence_info["ep"]));
goto C8ebf;
e5557:
if (isset($oc_version_check) && $oc_version_check == "\63") {
goto e28d6;
}
goto Ee1c4;
Cc563:
if (!($oc_version == "2.3")) {
goto bb9dc;
}
goto cbe62;
C5ef9:
$post_data = array("domain" => $dmt_config["domain"], "extension" => "30750", "id" => $dmt_config["licence"]);
goto A7e3c;
Faa7a:
goto E02f9;
goto E159f;
A1eb6:
$prefix = '';
goto B5477;
Dc0a9:
$domain = $this->request->server["SERVER_NAME"];
goto a828c;
f70e8:
Ea720:
goto bcb08;
Bd41b:
$data["template"] = self::DMT_CODE;
goto f213c;
B0f9d:
$tpl_data = array_merge($tpl_data, $tm_config);
goto cb470;
daadf:
/*curl_setopt($ch_test, CURLOPT_POSTFIELDS, http_build_query($post_data));*/
goto fc042;
A006c:
$this->document->addStyle("view/javascript/dmt/dmt.css");
goto F5663;
e8439:
$data["cancel"] = $this->url->link($ext_route, $this->token . "&type=analytics", true);
goto e1204;
cb470:
if (!empty($tpl_data["tagmanager"]["route_checkout"])) {
goto b0b6a;
}
goto B29d4;
Fac5a:
if (!isset($this->request->post)) {
goto E1c78;
}
goto D9e25;
Da005:
$data["breadcrumbs"][] = array("text" => $this->language->get("text_extension"), "href" => $this->url->link($ext_route, $this->token . "&type=analytics", true));
goto c09dd;
c199e:
$data["licencedomain"] = $domain;
goto C2d42;
e7a7c:
$filter->url = $this->url->link($tpl_route, $this->token . $curl_result . "&page={page}", true);
goto Ede06;
Dea5f:
$data["clear"] = $this->url->link($tpl_route . "/clear", $this->token . "&store_id=" . $store_id_param, true);
goto bcd90;
F4d06:
$tpl_data["button_apply"] = "Apply";
goto Fae71;
afb47:
/*curl_setopt($ch_test, CURLOPT_RETURNTRANSFER, true);*/
goto F7013;
A31f0:
b0b6a:
goto D0bab;
Be5c7:
$oc_version = substr(VERSION, 0, 3);
goto f30b6;
B361f:
$size_index = 0;
goto e345d;
D16c8:
A16dc:
goto b5aa7;
F5213:
$ext_route = "extension/analytics";
goto e23ab;
d1027:
$data = array_merge($data, $lang_data);
goto C3fd6;
B0e6c:
$tpl_data["error"] = false;
goto f5dd0;
A2702:
$data["domain"] = $domain;
goto c199e;
d9ccf:
$filter->limit = $limit;
goto e7a7c;
de8c4:
C7b0b:
goto e690d;
e3942:
if (!isset($this->request->get["store_id"])) {
goto E2023;
}
goto D4309;
Ca61f:
$this->response->redirect($this->url->link($tpl_route, $this->token . "&store_id=" . $store_id_param, true));
goto Bfc24;
c0285:
$post_data = array("module_mod_google" => $licence_info);
goto C7074;
cae64:
B5dac:
goto b71ab;
C4017:
if ($oc_version_check == "\63") {
goto d3b06;
}
goto A1eb6;
B7aff:
ebd4b:
goto E63ef;
fa2dc:
$tm_config = $this->model_extension_module_dmt->getConfig();
goto Ba6dc;
c26bb:
goto ac231;
goto f38df;
ff3f6:
$prefix = '';
goto Dcf20;
C495e:
/*$ch = curl_init();*/
goto D5ed1;
A1eea:
/*curl_close($ch);*/
goto e59f0;
E6983:
$data = array_merge($data, $tpl_data);
goto C4483;
Ede06:
$tpl_data["pagination"] = $filter->render();
goto F9bff;
fa263:
return;
goto a01de;
C3fd6:
$data["breadcrumbs"] = array();
goto f281d;
E4975:
foreach ($tpl_data["languages"] as &$language) {
goto a8869;
c4ff9:
B6026:
goto d8d31;
d89a1:
E7c86:
goto cb61b;
C6c55:
goto aeb47;
goto c4ff9;
f1662:
$language["image"] = "view/image/flags/" . $language["image"];
goto C6c55;
b5d53:
aeb47:
goto d89a1;
a8869:
if (version_compare(VERSION, "2.2", ">=")) {
goto B6026;
}
goto f1662;
d8d31:
$language["image"] = "language/" . $language["code"] . "/" . $language["code"] . ".png";
goto b5d53;
cb61b:
}
goto ac9c0;
C74dc:
$this->model_setting_setting->editSetting("module_mod_google", $post_data, $store_id_save);
goto b0649;
F5d91:
$tpl_route = "extension/analytics/tagmanager";
goto F94e5;
A14d9:
$tpl_data["tagmanager"] = $tm_settings;
goto fd0bc;
b866b:
$limit = 20;
goto b56cc;
c6874:
$prefix = self::SETTING_PREFIX;
goto a390a;
a1c44:
$settings = json_decode($settings, true);
goto d8fff;
E3b27:
if (!(!isset($tpl_data["tagmanager"]["code"]) || empty($tpl_data["tagmanager"]["code"]))) {
goto Ea720;
}
goto c11ac;
C6f7a:
goto Fb030;
goto e65f3;
De0c9:
$limit = (int) $this->config->get("config_limit_admin");
goto b5ab5;
B3173:
/*if (!$is_valid) {*/if (false) {
goto B3ecc;
}
goto A47f9;
F94f6:
/*curl_setopt($ch_test, CURLOPT_POST, true);*/
goto a62cd;
f30b6:
$oc_full_version = VERSION;
goto cdbe8;
A3b52:
if ($is_expired) {
goto e2282;
}
goto cdc60;
ffc97:
B0da2:
goto c8418;
F7013:
/*curl_setopt($ch_test, CURLOPT_CONNECTTIMEOUT, 30);*/
goto d4b87;
fb1de:
f7491:
goto Ccbcd;
d42ba:
$post_data = array("domain" => $domain, "extension" => "30750", "id" => $store_id);
goto B0e6c;
df317:
$data["breadcrumbs"] = array();
goto A2b59;
d4b87:
/*curl_setopt($ch_test, CURLOPT_TIMEOUT, 30);*/
goto F94f6;
Ecab3:
$store_id_param = 0;
goto Be5c7;
F460c:
$this->document->addScript("view/javascript/dmt/js/bootstrap-colorpicker.min.js");
goto ed01c;
E1821:
$prefix = '';
goto C6f7a;
ca7cc:
$licence_info = json_decode($licence_info, true);
goto b5ba1;
b3124:
$this->load->model("localisation/language");
goto f7d13;
c54c6:
$tpl_data["languages"] = $this->model_localisation_language->getLanguages();
goto Db5de;
Fd4ee:
goto ebeaf;
goto C1cdc;
e8ec6:
$data["column_left"] = $this->load->controller("common/column_left");
goto b0fc2;
ecac7:
$prefix = "analytics_";
goto e4925;
Fb411:
if (!($oc_version_check == "\61")) {
goto aab63;
}
goto afd8b;
Ed2ac:
goto A5e2e;
goto F5b4f;
Ee888:
$tpl_data["tagmanager"]["route_success"] = $tm_config["alt_success"];
goto a0749;
Fd8e8:
D525d:
goto cac66;
d8fff:
if (!($oc_major != "6")) {
goto aee9a;
}
goto bc7bc;
E815c:
D9a68:
goto C30c6;
C7476:
$curl_result = '';
goto d0070;
ffc13:
$data["header"] = $this->load->controller("common/header");
goto Dfca0;
B9721:
cf145:
goto ec7fd;
C30c6:
$licence_info = unserialize($licence_info);
goto bb07a;
Dcf20:
B73b6:
goto A754e;
C6b9a:
$this->response->setOutput($this->load->view("extension/analytics/dmt_licence", $data));
goto c70ad;
C7977:
$size_units = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
goto D6b21;
c70ad:
goto cf145;
goto B7aff;
F9fd9:
$ext_route = "extension/module";
goto ff3f6;
A7e3c:
$ep_value = $dmt_config["ep"];
goto d9d74;
F824c:
if (!($filesize / 1024 > 1)) {
goto B0da2;
}
goto b0918;
E9e76:
if (!(isset($settings["message"]) && $settings["message"] != "valid")) {
goto D9c10;
}
goto E1d3f;
D0bab:
if (!empty($tpl_data["tagmanager"]["route_success"])) {
goto b1423;
}
goto Ee888;
a6e40:
$this->cache->delete("tagmanager");
goto C0db2;
De988:
A6f5a:
goto F824c;
b0754:
$data["curl"] = $pagination;
goto e283a;
B201e:
$data["header"] = $this->load->controller("common/header");
goto e8ec6;
e690d:
$data["success"] = $this->session->data["success"];
goto f17b4;
E94ab:
if (!($size_index == "\61")) {
goto A29a2;
}
goto E25f0;
a3b0e:
$prefix = '';
goto da3be;
E0be8:
$tpl_data["tagmanager"] = isset($saved_settings[$prefix . "tagmanager_data"]) ? $saved_settings[$prefix . "tagmanager_data"] : false;
goto E3b27;
c62b1:
$this->document->addStyle("view/javascript/dmt/css/bootstrap-select.css");
goto F460c;
E1fe0:
E02f9:
goto fb1de;
f078e:
$this->config->set("template_engine", "template");
goto Edd89;
E3034:
$store_id_param = 0;
goto aaab2;
d7b77:
$data["action"] = $this->url->link($tpl_route, $this->token . "&store_id=" . $store_id_param, true);
goto B6f9c;
Aabbb:
Eab96:
goto b6fad;
B8715:
d4e5d:
goto A1eea;
b7629:
$this->model_setting_setting->editSetting($prefix . "tagmanager", $saved_settings, $store_id_param);
goto e5364;
ce8e6:
$tpl_data["stores"][] = array("store_id" => 0, "name" => "default");
goto d502f;
Eba66:
$total = $this->model_extension_module_dmt->getTotalTransactions($url_params, $store_id_param);
goto e22f8;
E7477:
C568c:
goto d63df;
A8f85:
$tpl_data["error"] = $settings["message"];
goto C3da9;
c5362:
ddb2c:
goto A14b8;
D187c:
Bd2dc:
goto c54c6;
a6c10:
$tpl_data["error_warning"] = '';
goto Cd53a;
Ce79e:
$data["refresh_url"] = $this->url->link($tpl_route, $this->token . "&store_id=" . $store_id_param, true);
goto Fd4ee;
e3e0a:
if (!($oc_version_check == "\63" || $oc_version == "\62\56\63")) {
goto Fd49d;
}
goto E94ab;
Bdee1:
if (!($this->check_array($tpl_data["stores"]) && count($tpl_data["stores"]) > 0)) {
goto d721f;
}
goto ce8e6;
F94e5:
$ext_route = "marketplace/extension";
goto Cc563;
e9c99:
if ($oc_version_check == "\62") {
goto fceab;
}
goto C4177;
e59f0:
$data = array_merge($data, $tpl_data);
goto e5557;
B580e:
if (isset($this->session->data["success"])) {
goto C7b0b;
}
goto c91b8;
Ba6af:
$this->document->setTitle($this->language->get("doc_title"));
goto Afd7a;
b6fad:
/*echo "Request Error:" . curl_error($ch);*/
goto B8715;
B841e:
if (!(isset($settings["message"]) && $settings["message"] == "valid")) {
goto B2fe1;
}
goto D2fca;
f577a:
$data["footer"] = $this->load->controller("common/footer");
goto C495e;
ecd5f:
$ext_route = "extension/extension";
goto Ab1bb;
A283f:
Acea6:
goto f70e8;
fccac:
$data["breadcrumbs"][] = array("text" => $this->language->get("text_extension"), "href" => $this->url->link($ext_route, $this->token . "&type=analytics", true));
goto c3f93;
f5654:
if (isset($this->request->post["datas"])) {
goto Bf90a;
}
goto c7220;
C4177:
goto ebeaf;
goto Ecb4a;
E9397:
if (isset($this->request->get["page"])) {
goto Dde47;
}
goto D7988;
D50a2:
$is_expired = $this->is_date_expired($saved_licence_date);
goto A3b52;
A14b8:
$this->config->set("template_engine", "template");
goto Adfee;
bb07a:
D1a48:
goto E1135;
E4077:
$tpl_data["show_order"] = true;
goto bd58b;
bafbc:
$this->response->redirect($this->url->link($ext_route, $this->token . "&type=analytics", true));
goto cbc3f;
Fa3c2:
bf71d:
goto B201e;
f17b4:
unset($this->session->data["success"]);
goto D187c;
a62cd:
/*curl_setopt($ch_test, CURLOPT_POSTFIELDS, http_build_query($post_data));*/
goto F5da4;
e65f3:
Ebdde:
goto ecac7;
ae4a8:
F542c:
goto B580e;
f449f:
$data["clear"] = $this->url->link($tpl_route . "/clear", $this->token . "&store_id=" . $store_id_param, true);
goto e8439;
Ce345:
$data["error"] = '';
goto ae39d;
ad805:
$filter->total = $total;
goto e405f;
b5aa7:
aee9a:
goto ae8dd;
f9103:
$data["refresh_url"] = $this->url->link($tpl_route, $this->token . "&store_id=" . $store_id_param, true);
goto ef9b6;
df903:
$is_valid = true; // was: $is_valid = false;
goto c0a47;
E7169:
$date_obj = DateTime::createFromFormat("d/m/Y", $data["date2"]);
goto C8c7e;
fc042:
/*$settings = curl_exec($ch_test);*/
goto D6c43;
d0070:
if (!isset($this->request->get["page"])) {
goto F4e22;
}
goto f5040;
b56cc:
F2b01:
goto F611d;
f5b22:
$tpl_data["PREFIX"] = $prefix;
goto B4f47;
afd8b:
$tpl_route = "module/tagmanager";
goto Fe30e;
debfb:
/*curl_close($ch_test);*/
goto a1c44;
d9d74:
$licence_hash = md5($dmt_config["email"] . $dmt_config["order_id"] . $dmt_config["domain"] . $ep_value);
goto Db246;
Cd53a:
goto C568c;
goto Fd8e8;
A6be4:
$this->response->setOutput($this->load->view("extension/analytics/dmt.tpl", $data));
goto Aee85;
Fc391:
$data["download_url"] = "https://aits.xyz/opencart/dmt/download?order_id=" . $data["order_id"] . "&email=" . $data["email"] . "&domain=" . $data["domain"];
goto c26bb;
da3be:
aab63:
goto A9882;
b5ba1:
goto D1a48;
goto E815c;
cac66:
$tpl_data["error_warning"] = $this->error["warning"];
goto E7477;
e5364:
$this->model_extension_module_dmt->writeConfig($this->request->post, $store_id_param);
goto E0323;
C38a6:
if (!empty($tpl_data["tagmanager"]["route_confirm"])) {
goto b2517;
}
goto E26b2;
C0db2:
$this->session->data["success"] = $this->language->get("text_success");
goto B361f;
C2c11:
b2517:
goto E6983;
aaab2:
b0e6b:
goto A7048;
E25f0:
$this->response->redirect($this->url->link($tpl_route, $this->token . "&store_id=" . $store_id_param, true));
goto B98af;
D944c:
Eafd9:
goto f5b22;
F5da4:
/*$settings = curl_exec($ch_test);*/
goto a1088;
Abba0:
if (!(isset($tm_settings["primary"]) && !empty($tm_settings["primary"]))) {
goto Aad60;
}
goto A14d9;
Fd551:
goto F04d9;
goto F2407;
d1398:
cb519:
goto B3173;
E7ae9:
$version_string = $this->model_extension_module_dmt->getNewURL();
goto c3bbc;
E9f17:
$this->load->language("extension/analytics/dmt");
goto Ba6af;
Dfb76:
$this->load->model("extension/module/dmt");
goto b3124;
e4925:
Fb030:
goto Ce345;
ab65b:
$tpl_data["tagmanager"] = $tm_settings;
goto d98be;
cb4cd:
$tpl_data["error"] = $settings["message"];
goto D16c8;
a10c5:
goto b0e6b;
goto C7de3;
Df1c0:
$data["module"] = self::EXT_ID;
goto Bd41b;
ae191:
if (curl_errno($ch)) {
goto Eab96;
}
goto b0754;
B0e23:
$tpl_data["error_primary"] = '';
goto b1a6f;
C7252:
$tpl_data["refund_url"] = $this->catalog . "index.php?route=" . $tpl_route . "/refund";
goto D9a54;
bd58b:
F04d9:
goto C7476;
e283a:
goto d4e5d;
goto Aabbb;
d63df:
if (isset($this->error["primary"])) {
goto B5dac;
}
goto B0e23;
E2abe:
if ($oc_version == "2.1" || $oc_version == "2.0") {
goto F9058;
}
goto e529c;
D9e25:
$saved_settings = array($prefix . "tagmanager_status" => $this->request->post[$prefix . "tagmanager_status"], $prefix . "tagmanager_data" => $this->request->post);
goto b7629;
d98be:
c5300:
goto Abba0;
C7de3:
E2023:
goto E3034;
C8256:
goto Ec763;
goto Dff31;
f12e7:
/*curl_setopt($ch_test, CURLOPT_CONNECTTIMEOUT, 30);*/
goto Ff165;
B98af:
A29a2:
goto bafbc;
B1ffe:
/*$pagination = curl_exec($ch);*/
goto ae191;
e345d:
$size_index = isset($this->request->post["apply"]) ? $this->request->post["apply"] : 0;
goto e3e0a;
E1d3f:
$tpl_data["error"] = $settings["message"];
goto df903;
A2b59:
$data["breadcrumbs"][] = array("text" => $this->language->get("text_home"), "href" => $this->url->link("common/dashboard", $this->token, true));
goto Da005;
ae39d:
$data["error_tm"] = '';
goto Dc0a9;
d0633:
$tpl_data["log"] = file_get_contents($log_file, FILE_USE_INCLUDE_PATH, null);
goto Faa7a;
C2d42:
$is_valid = true;
goto f5654;
Db246:
/*$ch_test = curl_init();*/
goto E13b5;
bea58:
goto A6f5a;
goto ffc97;
C0520:
$tpl_data["send_url"] = $this->catalog . "index.php?route=" . $tpl_route . "/sendorder";
goto C7252;
E159f:
d149d:
goto C7977;
bf97d:
a20fc:
goto fa263;
B6c3c:
$tpl_route = "module/tagmanager";
goto F9fd9;
Dff31:
fcdde:
goto F433d;
D6b21:
$page_num = 0;
goto De988;
b0649:
$is_valid = true; // was: $is_valid = false;
goto cb4cd;
ec7fd:
goto a20fc;
goto dc5cc;
Aee85:
A5e2e:
goto B2dc1;
B2dc1:
goto Da9b1;
goto c5362;
B72c1:
$data["download_url"] = false;
goto B3427;
b045a:
B3ecc:
goto ffc13;
ea43c:
D4fb1:
goto Cb912;
D7988:
$default_store = 1;
goto Fd551;
A1906:
$this->load->model("setting/setting");
goto Dfb76;
C8c7e:
$saved_licence_date = $date_obj->format("Y-m-d");
goto D50a2;
e4997:
Ec763:
goto C5ef9;
F5b4f:
F9058:
goto A6be4;
cc9c7:
if (!isset($licence_info["order_id"])) {
goto fcdde;
}
goto c6238;
C4483:
if (isset($data["date2"]) && !empty($data["date2"])) {
goto A884a;
}
goto C1c5e;
f5dd0:
/*$ch_test = curl_init();*/
goto Ef92b;
E1135:
eb758:
goto cc9c7;
a0749:
b1423:
goto C38a6;
d2acb:
$data["template"] = self::DMT_CODE;
goto df317;
Acda8:
$tpl_data["store_id"] = $store_id_param;
goto C4017;
eddef:
if (!(isset($settings["message"]) && $settings["message"] != "valid")) {
goto db550;
}
goto B4176;
ac9c0:
e79f5:
goto Aba05;
b9d20:
if (!(!isset($tpl_data["tagmanager"]["code"]) && !isset($tpl_data["tagmanager"]["customer_data"]) && !isset($tpl_data["tagmanager"]["admin"]))) {
goto Acea6;
}
goto af0f5;
bc7bc:
$empty_licence = array("order_id" => 0, "email" => '', "licence" => '', "status" => '', "domain" => '', "ep" => '', "date1" => '', "date2" => '');
goto E53c6;
c09dd:
$data["breadcrumbs"][] = array("text" => $this->language->get("doc_title"), "href" => $this->url->link($tpl_route, $this->token . "&store_id=" . $store_id_param, true));
goto d7b77;
Ff165:
/*curl_setopt($ch_test, CURLOPT_TIMEOUT, 30);*/
goto A92e1;
B3427:
ac231:
goto Fa3c2;
f7d13:
$this->load->model("setting/store");
goto B3e90;
c11ac:
$tm_settings = $this->model_extension_module_dmt->upgrade();
goto e2760;
c54c5:
$settings = json_decode($settings, true);
goto B841e;
d5416:
goto Bd2dc;
goto de8c4;
cbc3f:
Fd49d:
goto ea43c;
Ee1c4:
if ($oc_version == "2.1" || $oc_version == "2.0") {
goto ebd4b;
}
goto C6b9a;
cbe62:
$tpl_route = "extension/analytics/tagmanager";
goto ecd5f;
D74c4:
$filesize = filesize($log_file);
goto ea02e;
c7220:
$licence_info = $this->getSettingValue("module_mod_google", $store_id_save);
goto cdea7;
e405f:
$filter->page = $default_store;
goto d9ccf;
F433d:
$dmt_config = array("order_id" => 0, "email" => '', "licence" => '', "status" => '', "domain" => '', "ep" => '', "date1" => '', "date2" => '');
goto e4997;
a7aef:
$data["module"] = self::EXT_ID;
goto d2acb;
d9d37:
if (!file_exists($log_file)) {
goto f7491;
}
goto D74c4;
bcd90:
$data["user_token"] = $this->token;
goto Ce79e;
Dfe2a:
d3b06:
goto c6874;
B36ee:
$store_id = $this->request->post["datas"];
goto d42ba;
e22f8:
$tpl_data["order_total"] = $total;
goto aca81;
E26b2:
$tpl_data["tagmanager"]["route_confirm"] = $tm_config["alt_confirm"];
goto C2c11;
Db5de:
$saved_settings = $this->model_setting_setting->getSetting($prefix . "tagmanager", $store_id_param);
goto B85ca;
b0fc2:
$data["footer"] = $this->load->controller("common/footer");
goto B9e7e;
E0323:
E1c78:
goto a6e40;
D2fca:
$licence_info = array("order_id" => isset($settings["order_id"]) ? $settings["order_id"] : 0, "email" => isset($settings["email"]) ? $settings["email"] : '', "licence" => isset($settings["licence"]) ? $settings["licence"] : '', "status" => isset($settings["status"]) ? $settings["status"] : '', "domain" => isset($settings["domain"]) ? $settings["domain"] : '', "date1" => isset($settings["date1"]) ? $settings["date1"] : '', "date2" => isset($settings["date2"]) ? $settings["date2"] : '', "ep" => isset($settings["ep"]) ? $settings["ep"] : '');
goto c0285;
cfac4:
bb9dc:
goto Eee96;
Ad61f:
$page_num++;
goto bea58;
A9882:
if ($oc_version_check == "3") {
goto b0eb7;
}
goto e9c99;
De869:
$store_id_save = 0;
goto C05c9;
c8418:
$tpl_data["error_warning"] = sprintf($this->language->get("error_warning"), basename($log_file), round(substr($filesize, 0, strpos($filesize, "\56") + 4), 2) . $size_units[$page_num]);
goto E1fe0;
B4176:
$this->model_setting_setting->editSetting("module_mod_google", $post_data, $store_id_save);
goto cf89b;
a828c:
$domain = $this->gtmf($domain);
goto A2702;
Ee58d:
if ($oc_version_check == "3") {
goto Ebdde;
}
goto E1821;
f5040:
$curl_result .= "&page=" . $this->request->get["page"];
goto da492;
Afd7a:
$lang_data = $this->model_extension_module_dmt->getlang();
goto d1027;
dc5cc:
e28d6:
goto f078e;
d502f:
d721f:
goto Acda8;
ed01c:
$this->document->addScript("view/javascript/dmt/js/bootstrap-select.js");
goto B0f9d;
fd0bc:
Aad60:
goto b9d20;
C1cdc:
fceab:
goto Df1c0;
b1a6f:
goto F542c;
goto cae64;
F2675:
return;
goto F160a;
E8908:
if (!(!isset($tpl_data["tagmanager"]["vs"]) || empty($tpl_data["tagmanager"]["vs"]))) {
goto Eafd9;
}
goto E7ae9;
C9784:
$this->document->setTitle($this->language->get("doc_title"));
goto a7aef;
B5782:
$tpl_data["image_url"] = "view/javascript/dmt/img/";
goto F5d91;
e2760:
if (!(isset($tm_settings["code"]) && !empty($tm_settings["code"]))) {
goto c5300;
}
goto ab65b;
Cee21:
/*curl_setopt($ch_test, CURLOPT_RETURNTRANSFER, true);*/
goto f12e7;
ea02e:
if ($filesize >= 5242880) {
goto d149d;
}
goto d0633;
c3f93:
$data["breadcrumbs"][] = array("text" => $this->language->get("doc_title"), "href" => $this->url->link($tpl_route, $this->token . "&store_id=" . $store_id_param, true));
goto e4e3b;
fe263:
$dmt_config["date2"] = isset($licence_info["date2"]) ? base64_decode($licence_info["date2"]) : '';
goto C8256;
c91b8:
$data["success"] = '';
goto d5416;
b5ab5:
if (!($limit < 1)) {
goto F2b01;
}
goto b866b;
e4e3b:
$data["action"] = $this->url->link($tpl_route, $this->token . "&store_id=" . $store_id_param, true);
goto f449f;
cdbe8:
$tpl_data = array();
goto e3942;
cf89b:
$is_valid = true; // was: $is_valid = false;
goto A8f85;
Fe30e:
$ext_route = "extension/module";
goto a3b0e;
F9bff:
$tpl_data["results"] = sprintf($this->language->get("text_pagination"), $total ? ($default_store - 1) * $limit + 1 : 0, ($default_store - 1) * $limit > $total - $limit ? $total : ($default_store - 1) * $limit + $limit, $total, ceil($total / $limit));
goto F4d06;
D6c43:
/*$oc_major = curl_errno($ch_test);*/
goto debfb;
A754e:
if (!($oc_version == "2.1" || $oc_version == "2.2")) {
goto a609d;
}
goto f06d5;
Fae71:
$tpl_data["currencies"] = array();
goto fa3e5;
A47f9:
$data = array_merge($data, $dmt_config);
goto C7b90;
D4309:
$store_id_param = $this->request->get["store_id"];
goto a10c5;
Eee96:
if (!($oc_version == "\62\56\60")) {
goto B73b6;
}
goto B6c3c;
Adfee:
$this->response->setOutput($this->load->view("extension/analytics/" . $data["template"], $data));
goto C2906;
B5477:
goto Cef0c;
goto Dfe2a;
B6f9c:
$data["cancel"] = $this->url->link($ext_route, $this->token . "&type=analytics", true);
goto Dea5f;
A7048:
$tpl_data["stores"] = $this->model_setting_store->getStores();
goto Bdee1;
Cb912:
if (isset($this->error["warning"])) {
goto D525d;
}
goto a6c10;
Dfca0:
$data["column_left"] = $this->load->controller("common/column_left");
goto f577a;
f213c:
$data["ver"] = "2x";
goto E9f17;
c0a47:
D9c10:
goto d1398;
af0f5:
$tpl_data["tagmanager"] = $tm_settings;
goto A283f;
bcb08:
$tpl_data["tagmanager"] = $this->model_extension_module_dmt->GetTagmanagerVariables($tpl_data["tagmanager"], $store_id_param);
goto E8908;
E13b5:
/*curl_setopt($ch_test, CURLOPT_URL, "https://licence.aits.xyz/verify.php");*/
goto Cee21;
B9e7e:
if (isset($oc_version_check) && $oc_version_check == "3") {
goto ddb2c;
}
goto E2abe;
da492:
F4e22:
goto De0c9;
C7b90:
goto Ab041;
goto b045a;
B85ca:
$tpl_data["tagmanager_status"] = isset($saved_settings[$prefix . "tagmanager_status"]) ? $saved_settings[$prefix . "tagmanager_status"] : false;
goto E0be8;
Bfc24:
B2fe1:
goto E9e76;
D9a54:
$tpl_data["tagmanager_settings"] = $this->model_extension_module_dmt->getTagmanger();
goto af012;
F5663:
$this->document->addStyle("view/javascript/dmt/css/bootstrap-colorpicker.min.css");
goto c62b1;
a390a:
Cef0c:
goto B5782;
E47f2:
$oc_version = substr(VERSION, 0, 3);
goto Ee58d;
A776b:
$data["your_status"] = "Expired";
goto B72c1;
ad1e9:
goto bf71d;
goto D0152;
aca81:
$tpl_data["page"] = $default_store;
goto Bcd83;
D5ed1:
/*curl_setopt($ch, CURLOPT_URL, "https://licence.aits.xyz/curl.html");*/
goto e8750;
b7287:
if (!($this->request->server["REQUEST_METHOD"] == "POST" && $this->validate())) {
goto D4fb1;
}
goto Fac5a;
A92e1:
/*curl_setopt($ch_test, CURLOPT_POST, true);*/
goto daadf;
Ab1bb:
$prefix = '';
goto cfac4;
Ccbcd:
$tpl_data["show_order"] = false;
goto E9397;
C8ebf:
$dmt_config["date1"] = isset($licence_info["date1"]) ? base64_decode($licence_info["date1"]) : '';
goto fe263;
e529c:
$this->response->setOutput($this->load->view("extension/analytics/dmt", $data));
goto Ed2ac;
cdea7:
if (!(isset($licence_info) && !empty($licence_info))) {
goto eb758;
}
goto A6891;
f38df:
e2282:
goto A776b;
B6894:
$default_store = (int) $this->request->get["page"];
goto E4077;
d4c17:
$log_file = DIR_LOGS . "dmt.log";
goto d9d37;
C3da9:
db550:
goto B4d6d;
a5907:
$filter = new Pagination();
goto ad805;
B4f47:
$this->load->model("localisation/currency");
goto E4975;
bcec0:
$this->load->language("extension/analytics/dmt");
goto C9784;
F2407:
Dde47:
goto B6894;
Aba05:
$tpl_data["log"] = '';
goto d4c17;
F611d:
$url_params = array("start" => ($default_store - 1) * $limit, "limit" => $limit);
goto fa2dc;
fa3e5:
$tpl_data["currencies"] = $this->model_localisation_currency->getCurrencies();
goto C0520;
C1c5e:
$data["your_status"] = "Unkown";
goto e925d;
Ef92b:
/*curl_setopt($ch_test, CURLOPT_URL, "https://licence.aits.xyz/verify.php");*/
goto afb47;
C05c9:
$oc_version_check = substr(VERSION, 0, 1);
goto fe4ca;
e1204:
$data["token"] = $this->token;
goto f9103;
b71ab:
$tpl_data["error_primary"] = $this->error["primary"];
goto ae4a8;
E53c6:
$post_data = array("module_mod_google" => $empty_licence);
goto eddef;
Edd89:
$this->response->setOutput($this->load->view("extension/analytics/dmt_licence", $data));
goto bf97d;
a1088:
/*curl_close($ch_test);*/
goto c54c5;
B3e90:
$oc_version_check = substr(VERSION, 0, 1);
goto Ecab3;
F160a:
}
public function is_date_expired($date_input)
{
goto dee0d;
E3307:
a845a:
goto Db9ff;
b7ea2:
goto A99bd;
goto E3307;
Db9ff:
return true;
goto dbfb6;
E2665:
return false;
goto b7ea2;
Cc065:
if ($check_date < $now) {
goto a845a;
}
goto E2665;
C0286:
try {
$check_date = new DateTime($date_input);
} catch (Exception $exception) {
return false;
}
goto Cc065;
dee0d:
$now = new DateTime();
goto C0286;
dbfb6:
A99bd:
goto db0ad;
db0ad:
}
public function clear()
{
goto D607a;
Ef33a:
if ($oc_version == "2.1" || $oc_version == "2.2") {
goto A3836;
}
goto d15ba;
E2ea5:
goto De140;
goto ba188;
fa495:
if ($oc_version_check == "1") {
goto c3b26;
}
goto Ef33a;
D9460:
goto De140;
goto A5212;
c5302:
$this->redirect($this->url->link("module/tagmanager", $this->token, "SSL"));
goto d19fa;
A5212:
df026:
goto ba977;
D607a:
$log_file = DIR_LOGS . "dmt.log";
goto C6ca1;
a374f:
$this->response->redirect($this->url->link("extension/analytics/tagmanager", $this->token . "&store_id=" . $store_id_param, "SSL"));
goto E2ea5;
eafce:
$file_handle = fopen($log_file, "w+");
goto f3569;
C6ca1:
$oc_version_check = substr(VERSION, 0, 1);
goto B5a92;
ba188:
A3836:
goto bb28e;
B5a92:
$store_id_param = 0;
goto cbbf6;
ee1ac:
Adad3:
goto E9b92;
Fede2:
De140:
goto Ee346;
Ae9d6:
goto d8c00;
goto cb5f5;
eea2c:
if ($oc_version_check == "\63") {
goto Adad3;
}
goto fa495;
De621:
$this->session->data["success"] = "Log cleared";
goto eea2c;
d15ba:
if ($oc_version == "2.0") {
goto df026;
}
goto a374f;
ba977:
$this->redirect($this->url->link("module/tagmanager", $this->token . "&store_id=" . $store_id_param, "SSL"));
goto Fede2;
d19fa:
d8c00:
goto dbeb2;
cb5f5:
c3b26:
goto c5302;
Ee346:
goto d8c00;
goto ee1ac;
bb28e:
$this->response->redirect($this->url->link("analytics/tagmanager", $this->token . "&store_id=" . $store_id_param, "SSL"));
goto D9460;
f3569:
fclose($file_handle);
goto De621;
cbbf6:
$oc_version = substr(VERSION, 0, 3);
goto eafce;
E9b92:
$this->response->redirect($this->url->link("extension/analytics/tagmanager", $this->token . "&store_id=" . $store_id_param, true));
goto Ae9d6;
dbeb2:
}
public function gtmf($data, $check_licence = false)
{
goto c5cb1;
Fe0c6:
a0408:
goto b32d7;
fac7d:
F770c:
goto C5062;
Bd917:
Fa22c:
goto Ff97d;
a9516:
if (!(count($parts) === 1)) {
goto F770c;
}
goto A136a;
e031a:
if (!filter_var($domain, FILTER_VALIDATE_IP)) {
goto b7391;
}
goto E1fad;
C5bb5:
$parts = array_values($parts);
goto Ba09d;
e53fc:
goto Fa22c;
goto Fe0c6;
c5cb1:
$this->load->model("extension/module/dmt");
goto d5878;
Ff97d:
return implode(".", array_slice($parts, -2));
goto cb91f;
cf1fc:
b7391:
goto ed7c0;
E1fad:
return $domain;
goto cf1fc;
df632:
abea4:
goto c8580;
b32d7:
return implode("\56", array_slice($parts, -3));
goto A1270;
e868a:
if (in_array($parts[count($parts) - 1], $tld_list)) {
goto D0a53;
}
goto Cdfeb;
C5062:
$tld_parts = implode(".", array_slice($parts, -2));
goto b1b4c;
Cdfeb:
goto Fa22c;
goto df632;
F7ec3:
return implode(".", array_slice($parts, -2));
goto Bd917;
b1b4c:
$apply = implode("\56", array_slice($parts, -3));
goto F9db6;
F9db6:
if (in_array($apply, $tld_list)) {
goto abea4;
}
goto Bed06;
ed7c0:
$parts = array_filter(explode(".", $domain), function ($segment) {
return !in_array($segment, ["www", "dev", "test", "demo"]);
});
goto C5bb5;
Bed06:
if (in_array($tld_parts, $tld_list)) {
goto a0408;
}
goto e868a;
A136a:
return $parts[0];
goto fac7d;
Ba09d:
$tld_list = $this->model_extension_module_dmt->getTLD();
goto a9516;
f9d26:
D0a53:
goto F7ec3;
d5878:
$shifted = $domain = strtolower($data);
goto e031a;
A1270:
goto Fa22c;
goto f9d26;
c8580:
return implode("\56", array_slice($parts, -4));
goto e53fc;
cb91f:
}
protected function validate()
{
goto Ead34;
B819e:
if (!($oc_version == "2.3")) {
goto a0d37;
}
goto Bf280;
b576b:
$route = "module/tagmanager";
goto Ea400;
B4ae5:
if (!($oc_version_check == "\61" || $oc_version_check == "\62")) {
goto E8a10;
}
goto b576b;
Ea400:
E8a10:
goto ce481;
C0ace:
$prefix = '';
goto Ea6d6;
A0d99:
return !$this->error;
goto A709c;
Ff371:
$route = "analytics/tagmanager";
goto E2c73;
Ead34:
$oc_version_check = substr(VERSION, 0, 1);
goto C0ace;
E2c73:
E9060:
goto B819e;
Ea6d6:
$store_id_param = 0;
goto a847b;
bb621:
d0593:
goto A0d99;
a847b:
$oc_version = substr(VERSION, 0, 3);
goto A7598;
ce481:
if (!($oc_version == "2.1" || $oc_version == "2.2")) {
goto E9060;
}
goto Ff371;
A7598:
$route = "extension/analytics/tagmanager";
goto B4ae5;
bcfc1:
if ($this->user->hasPermission("modify", $route)) {
goto d0593;
}
goto Cde96;
D7f9d:
a0d37:
goto bcfc1;
Cde96:
$this->error["warning"] = $this->language->get("error_permission");
goto bb621;
Bf280:
$route = "extension/analytics/tagmanager";
goto D7f9d;
A709c:
}
public function install()
{
$this->load->model("extension/module/dmt");
$this->model_extension_module_dmt->createDB();
}
public function uninstall()
{
$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "analytics_tracking`");
}
private function updateDatabase()
{
$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "analytics_tracking` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`order_id` int(15) DEFAULT NULL,
			`cid` varchar(128) DEFAULT NULL,
			`uid` varchar(64) DEFAULT NULL,
			`ip` varchar(64) DEFAULT NULL,
			`geoid` varchar(64) DEFAULT NULL,
			`sr` varchar(64) DEFAULT NULL,
			`vp` varchar(64) DEFAULT NULL,
			`ul` varchar(64) DEFAULT NULL,
			`dr` varchar(250) DEFAULT NULL,
			`tid` varchar(24) DEFAULT NULL,
			`user_agent` varchar(250) DEFAULT NULL,
			`currency_code` varchar(11) DEFAULT NULL,
			`currency_id` int(11) DEFAULT NULL,
			`event_id` varchar(64) DEFAULT NULL,
			`fbp` varchar(100) DEFAULT NULL,
			`fbc` varchar(100) DEFAULT NULL,
			`ttp` varchar(100) DEFAULT NULL,
			`ttclid` varchar(100) DEFAULT NULL,
			`sc_click_id` varchar(100) DEFAULT NULL,
			`sc_cookie1` varchar(100) DEFAULT NULL,
			`hit` tinyint(1) NOT NULL DEFAULT '0',
			`hit_ga` tinyint(1) NOT NULL DEFAULT 0,
			`hit_fb` tinyint(1) NOT NULL DEFAULT 0,
			`hit_tiktok` tinyint(1) NOT NULL DEFAULT 0,
			`hit_snapchat` tinyint(1) NOT NULL DEFAULT 0,
			`refund` tinyint(1) NOT NULL DEFAULT 0,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
}
private function columnExistsInTable($table_name, $column_name)
{
goto b02f1;
d42f4:
foreach ($query_result->rows as $row) {
goto Efe06;
d72d0:
a89f5:
goto af371;
af371:
A326c:
goto Cb453;
Efe06:
if (!($row["Field"] == $column_name)) {
goto a89f5;
}
goto D4328;
D4328:
return true;
goto d72d0;
Cb453:
}
goto df33b;
df33b:
b1a80:
goto F8cad;
b02f1:
$query_result = $this->db->query("DESC `" . DB_PREFIX . $table_name . "`;");
goto d42f4;
F8cad:
return false;
goto e3505;
e3505:
}
private function getSettingValue($setting_key, $store_id_param = 0)
{
goto C6b58;
B15e0:
goto A6143;
goto C4120;
f4035:
return null;
goto B15e0;
Be27a:
return $query_result->row["value"];
goto fb2ea;
C73bc:
if ($query_result->num_rows) {
goto b0c9e;
}
goto f4035;
C6b58:
$query_result = $this->db->query("SELECT value FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int) $store_id_param . "' AND `key` = '" . $this->db->escape($setting_key) . "'");
goto C73bc;
C4120:
b0c9e:
goto Be27a;
fb2ea:
A6143:
goto Bba3e;
Bba3e:
}
private function URLredirect($curl_result, $status_code = 302)
{
header("Location: " . str_replace(array("&amp;", "\xa", "\xd"), array("&", '', ''), $curl_result), true, $status_code);
exit;
}
public function check_array($input_val)
{
return is_array($input_val) || $input_val instanceof \Countable || $input_val instanceof \SimpleXMLElement || $input_val instanceof \ResourceBundle;
}
}
?>