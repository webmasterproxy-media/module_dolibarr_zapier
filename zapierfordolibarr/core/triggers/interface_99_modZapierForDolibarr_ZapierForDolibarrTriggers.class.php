<?php
/* Copyright (C) 2017-2019  Frédéric France     <frederic.france@netlogic.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    core/triggers/interface_99_modZapierForDolibarr_ZapierForDolibarrTriggers.class.php
 * \ingroup zapierfordolibarr
 * \brief   Example trigger.
 *
 *
 * \remarks You can create other triggers by copying this one.
 * - File name should be either:
 *      - interface_99_modZapierForDolibarr_MyTrigger.class.php
 *      - interface_99_all_MyTrigger.class.php
 * - The file must stay in core/triggers
 * - The class name must be InterfaceMytrigger
 * - The constructor method must be named InterfaceMytrigger
 * - The name property name must be MyTrigger
 */

require_once DOL_DOCUMENT_ROOT.'/core/triggers/dolibarrtriggers.class.php';


/**
 *  Class of triggers for ZapierForDolibarr module
 */
class InterfaceZapierForDolibarrTriggers extends DolibarrTriggers
{
    /**
     * @var DoliDB Database handler
     */
    protected $db;

    /**
     * Constructor
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;

        $this->name = preg_replace('/^Interface/i', '', get_class($this));
        $this->family = "demo";
        $this->description = "ZapierForDolibarr triggers.";
        // 'development', 'experimental', 'dolibarr' or version
        $this->version = 'dolibarr';
        $this->picto = 'zapierfordolibarr@zapierfordolibarr';
    }

    /**
     * Trigger name
     *
     * @return string Name of trigger file
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Trigger description
     *
     * @return string Description of trigger file
     */
    public function getDesc()
    {
        return $this->description;
    }


    /**
     * Function called when a Dolibarrr business event is done.
     * All functions "runTrigger" are triggered if file
     * is inside directory core/triggers
     *
     * @param string        $action     Event action code
     * @param CommonObject  $object     Object
     * @param User          $user       Object user
     * @param Translate     $langs      Object langs
     * @param Conf          $conf       Object conf
     * @return int                      <0 if KO, 0 if no triggered ran, >0 if OK
     */
    public function runTrigger($action, $object, User $user, Translate $langs, Conf $conf)
    {
        global $db;
        if (empty($conf->zapierfordolibarr->enabled)) {
            // Module not active, we do nothing
            return 0;
        }
        $logtriggeraction = false;
        if ($action!='') {
            $actions = explode('_', $action);
            $sql = 'SELECT rowid, url FROM '.MAIN_DB_PREFIX.'zapierfordolibarr_hook WHERE';
            $sql .= ' module="'.$db->escape(strtolower($actions[0])).'" AND action="'.$db->escape(strtolower($actions[1])).'"';
            //setEventMessages($sql, null);
        }

        switch ($action) {

            // Users
            //case 'USER_CREATE':
            //case 'USER_MODIFY':
            //case 'USER_NEW_PASSWORD':
            //case 'USER_ENABLEDISABLE':
            //case 'USER_DELETE':
            //case 'USER_SETINGROUP':
            //case 'USER_REMOVEFROMGROUP':

            case 'USER_LOGIN':
                //$logtriggeraction = true;
                break;
            case 'USER_LOGIN_FAILED':
                //$logtriggeraction = true;
                break;
            case 'USER_LOGOUT':
                //$logtriggeraction = true;
                break;
            // Warning: To increase performances, this action is triggered only if constant MAIN_ACTIVATE_UPDATESESSIONTRIGGER is set to 1.
            //case 'USER_UPDATE_SESSION':

            case 'DOSSIERISOLATION_CREATE':
                //$logtriggeraction = true;
                break;
            case 'DOSSIERISOLATION_MODIFY':
                //$logtriggeraction = true;
                break;
            case 'DOSSIERISOLATION_DELETE':
                //$logtriggeraction = true;
                break;

            // Actions
            case 'ACTION_MODIFY':
                //$logtriggeraction = true;
                break;
            case 'ACTION_CREATE':
                $resql = $db->query($sql);
                // TODO voir comment regrouper les webhooks en un post
                while ($resql && $obj = $db->fetch_array($resql)) {
                    $cleaned = cleanObjectDatas(dol_clone($object));
                    $cleaned = cleanAgendaEventsDatas($cleaned);
                    $json = json_encode($cleaned);
                    //mail('frederic.france@free.fr', 'test agenda', print_r($json, true));
                    $headers = array('Accept: application/json', 'Content-Type: application/json');
                    // call the zapierPostWebhook() function
                    zapierPostWebhook($obj['url'], $json, $headers);
                    //setEventMessages($obj['url'], null);
                }
                $logtriggeraction = true;
                break;
            case 'ACTION_DELETE':
                //$logtriggeraction = true;
                break;

            // Groups
            //case 'GROUP_CREATE':
            //case 'GROUP_MODIFY':
            //case 'GROUP_DELETE':

            // Companies
            case 'COMPANY_CREATE':
                $resql = $db->query($sql);
                while ($resql && $obj = $db->fetch_array($resql)) {
                    $cleaned = cleanObjectDatas(dol_clone($object));
                    $json = json_encode($cleaned);
                    $headers = array('Accept: application/json', 'Content-Type: application/json');
                    // call the zapierPostWebhook() function
                    zapierPostWebhook($obj['url'], $json, $headers);
                }
                $logtriggeraction = true;
                break;
            case 'COMPANY_MODIFY':
                $resql = $db->query($sql);
                while ($resql && $obj = $db->fetch_array($resql)) {
                    $cleaned = cleanObjectDatas(dol_clone($object));
                    $json = json_encode($cleaned);
                    $headers = array('Accept: application/json', 'Content-Type: application/json');
                    // call the zapierPostWebhook() function
                    zapierPostWebhook($obj['url'], $json, $headers);
                }
                $logtriggeraction = true;
                break;
            case 'COMPANY_DELETE':
                //$logtriggeraction = true;
                break;

            // Contacts
            case 'CONTACT_CREATE':
            case 'CONTACT_MODIFY':
            case 'CONTACT_DELETE':
            case 'CONTACT_ENABLEDISABLE':

            // Products
            case 'PRODUCT_CREATE':
            case 'PRODUCT_MODIFY':
            case 'PRODUCT_DELETE':
            case 'PRODUCT_PRICE_MODIFY':
            case 'PRODUCT_SET_MULTILANGS':
            case 'PRODUCT_DEL_MULTILANGS':

            //Stock mouvement
            case 'STOCK_MOVEMENT':

            //MYECMDIR
            case 'MYECMDIR_DELETE':
            case 'MYECMDIR_CREATE':
            case 'MYECMDIR_MODIFY':

            // Customer orders
            case 'ORDER_CREATE':
                $resql = $db->query($sql);
                while ($resql && $obj = $db->fetch_array($resql)) {
                    $cleaned = cleanObjectDatas(dol_clone($object));
                    $json = json_encode($cleaned);
                    $headers = array('Accept: application/json', 'Content-Type: application/json');
                    // call the zapierPostWebhook() function
                    zapierPostWebhook($obj['url'], $json, $headers);
                }
                $logtriggeraction = true;
                break;
            case 'ORDER_CLONE':
                break;
            case 'ORDER_VALIDATE':
                break;
            case 'ORDER_DELETE':
            case 'ORDER_CANCEL':
            case 'ORDER_SENTBYMAIL':
            case 'ORDER_CLASSIFY_BILLED':
            case 'ORDER_SETDRAFT':
            case 'LINEORDER_INSERT':
            case 'LINEORDER_UPDATE':
            case 'LINEORDER_DELETE':

            // Supplier orders
            case 'ORDER_SUPPLIER_CREATE':
            case 'ORDER_SUPPLIER_CLONE':
            case 'ORDER_SUPPLIER_VALIDATE':
            case 'ORDER_SUPPLIER_DELETE':
            case 'ORDER_SUPPLIER_APPROVE':
            case 'ORDER_SUPPLIER_REFUSE':
            case 'ORDER_SUPPLIER_CANCEL':
            case 'ORDER_SUPPLIER_SENTBYMAIL':
            case 'ORDER_SUPPLIER_DISPATCH':
            case 'LINEORDER_SUPPLIER_DISPATCH':
            case 'LINEORDER_SUPPLIER_CREATE':
            case 'LINEORDER_SUPPLIER_UPDATE':

            // Proposals
            case 'PROPAL_CREATE':
            case 'PROPAL_CLONE':
            case 'PROPAL_MODIFY':
            case 'PROPAL_VALIDATE':
            case 'PROPAL_SENTBYMAIL':
            case 'PROPAL_CLOSE_SIGNED':
                //$logtriggeraction = true;
                break;
            case 'PROPAL_CLOSE_REFUSED':
                //$logtriggeraction = true;
                break;
            case 'PROPAL_DELETE':
                //$logtriggeraction = true;
                break;
            case 'LINEPROPAL_INSERT':
            case 'LINEPROPAL_UPDATE':
            case 'LINEPROPAL_DELETE':

            // SupplierProposal
            case 'SUPPLIER_PROPOSAL_CREATE':
            case 'SUPPLIER_PROPOSAL_CLONE':
            case 'SUPPLIER_PROPOSAL_MODIFY':
            case 'SUPPLIER_PROPOSAL_VALIDATE':
            case 'SUPPLIER_PROPOSAL_SENTBYMAIL':
            case 'SUPPLIER_PROPOSAL_CLOSE_SIGNED':
            case 'SUPPLIER_PROPOSAL_CLOSE_REFUSED':
            case 'SUPPLIER_PROPOSAL_DELETE':
            case 'LINESUPPLIER_PROPOSAL_INSERT':
            case 'LINESUPPLIER_PROPOSAL_UPDATE':
            case 'LINESUPPLIER_PROPOSAL_DELETE':

            // Contracts
            case 'CONTRACT_CREATE':
            case 'CONTRACT_ACTIVATE':
            case 'CONTRACT_CANCEL':
            case 'CONTRACT_CLOSE':
            case 'CONTRACT_DELETE':
            case 'LINECONTRACT_INSERT':
            case 'LINECONTRACT_UPDATE':
            case 'LINECONTRACT_DELETE':

            // Bills
            case 'BILL_CREATE':
                //$logtriggeraction = true;
                break;
            case 'BILL_CLONE':
            case 'BILL_MODIFY':
            case 'BILL_VALIDATE':
            case 'BILL_UNVALIDATE':
                //$logtriggeraction = true;
                break;
            case 'BILL_SENTBYMAIL':
                //$logtriggeraction = true;
                break;
            case 'BILL_CANCEL':
                //$logtriggeraction = true;
                break;
            case 'BILL_DELETE':
                //$logtriggeraction = true;
                break;
            case 'BILL_PAYED':
            case 'LINEBILL_INSERT':
            case 'LINEBILL_UPDATE':
            case 'LINEBILL_DELETE':

            //Supplier Bill
            case 'BILL_SUPPLIER_CREATE':
            case 'BILL_SUPPLIER_UPDATE':
            case 'BILL_SUPPLIER_DELETE':
            case 'BILL_SUPPLIER_PAYED':
            case 'BILL_SUPPLIER_UNPAYED':
            case 'BILL_SUPPLIER_VALIDATE':
            case 'BILL_SUPPLIER_UNVALIDATE':
            case 'LINEBILL_SUPPLIER_CREATE':
            case 'LINEBILL_SUPPLIER_UPDATE':
            case 'LINEBILL_SUPPLIER_DELETE':

                // Payments
            case 'PAYMENT_CUSTOMER_CREATE':
            case 'PAYMENT_SUPPLIER_CREATE':
            case 'PAYMENT_ADD_TO_BANK':
            case 'PAYMENT_DELETE':

                // Online
            case 'PAYMENT_PAYBOX_OK':
            case 'PAYMENT_PAYPAL_OK':
            case 'PAYMENT_STRIPE_OK':

                // Donation
            case 'DON_CREATE':
            case 'DON_UPDATE':
            case 'DON_DELETE':

                // Interventions
            case 'FICHINTER_CREATE':
            case 'FICHINTER_MODIFY':
            case 'FICHINTER_VALIDATE':
            case 'FICHINTER_DELETE':
            case 'LINEFICHINTER_CREATE':
            case 'LINEFICHINTER_UPDATE':
            case 'LINEFICHINTER_DELETE':

                // Members
            case 'MEMBER_CREATE':
            case 'MEMBER_VALIDATE':
            case 'MEMBER_SUBSCRIPTION':
            case 'MEMBER_MODIFY':
            case 'MEMBER_NEW_PASSWORD':
            case 'MEMBER_RESILIATE':
            case 'MEMBER_DELETE':

                // Categories
            case 'CATEGORY_CREATE':
            case 'CATEGORY_MODIFY':
            case 'CATEGORY_DELETE':
            case 'CATEGORY_SET_MULTILANGS':

                // Projects
            case 'PROJECT_CREATE':
            case 'PROJECT_MODIFY':
            case 'PROJECT_DELETE':

                // Project tasks
            case 'TASK_CREATE':
            case 'TASK_MODIFY':
            case 'TASK_DELETE':

                // Task time spent
            case 'TASK_TIMESPENT_CREATE':
            case 'TASK_TIMESPENT_MODIFY':
            case 'TASK_TIMESPENT_DELETE':

                // Shipping
            case 'SHIPPING_CREATE':
            case 'SHIPPING_MODIFY':
            case 'SHIPPING_VALIDATE':
            case 'SHIPPING_SENTBYMAIL':
            case 'SHIPPING_BILLED':
            case 'SHIPPING_CLOSED':
            case 'SHIPPING_REOPEN':
                //$logtriggeraction = true;
                break;
            case 'SHIPPING_DELETE':
                //$logtriggeraction = true;
                break;
            }
        if ($logtriggeraction) {
            dol_syslog("Trigger '" . $this->name . "' for action '.$action.' launched by " . __FILE__ . " id=" . $object->id);
        }
        return 0;
    }
}

function zapierPostWebhook($url, $json, $headers)
{
    // TODO supprimer le webhook en cas de mauvaise réponse
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $output = curl_exec($ch);
    curl_close($ch);
}

/**
 * Clean sensible object datas
 *
 * @param   object  $toclean    Object to clean
 * @return  array   Array of cleaned object properties
 */
function cleanObjectDatas($toclean)
{
    // Remove $db object property for object
    unset($toclean->db);

    // Remove linkedObjects. We should already have linkedObjectIds that avoid huge responses
    unset($toclean->linkedObjects);

    unset($toclean->lines); // should be ->lines

    unset($toclean->fields);

    unset($toclean->oldline);

    unset($toclean->error);
    unset($toclean->errors);

    unset($toclean->ref_previous);
    unset($toclean->ref_next);
    unset($toclean->ref_int);

    unset($toclean->projet);     // Should be fk_project
    unset($toclean->project);    // Should be fk_project
    unset($toclean->author);     // Should be fk_user_author
    unset($toclean->timespent_old_duration);
    unset($toclean->timespent_id);
    unset($toclean->timespent_duration);
    unset($toclean->timespent_date);
    unset($toclean->timespent_datehour);
    unset($toclean->timespent_withhour);
    unset($toclean->timespent_fk_user);
    unset($toclean->timespent_note);

    unset($toclean->statuts);
    unset($toclean->statuts_short);
    unset($toclean->statuts_logo);
    unset($toclean->statuts_long);

    unset($toclean->element);
    unset($toclean->fk_element);
    unset($toclean->table_element);
    unset($toclean->table_element_line);
    unset($toclean->picto);

    unset($toclean->skip_update_total);
    unset($toclean->context);

    // Remove the $oldcopy property because it is not supported by the JSON
    // encoder. The following error is generated when trying to serialize
    // it: "Error encoding/decoding JSON: Type is not supported"
    // Note: Event if this property was correctly handled by the JSON
    // encoder, it should be ignored because keeping it would let the API
    // have a very strange behavior: calling PUT and then GET on the same
    // resource would give different results:
    // PUT /objects/{id} -> returns object with oldcopy = previous version of the object
    // GET /objects/{id} -> returns object with oldcopy empty
    unset($toclean->oldcopy);

    // If object has lines, remove $db property
    if (isset($toclean->lines) && count($toclean->lines) > 0)  {
        $nboflines = count($toclean->lines);
        for ($i=0; $i < $nboflines; $i++) {
            $this->cleanObjectDatas($toclean->lines[$i]);
        }
    }

    // If object has linked objects, remove $db property
    /*
    if(isset($toclean->linkedObjects) && count($toclean->linkedObjects) > 0)  {
        foreach($toclean->linkedObjects as $type_object => $linked_object) {
            foreach($linked_object as $toclean2clean) {
                $this->cleanObjectDatas($toclean2clean);
            }
        }
    }*/

    return $toclean;
}
/**
 * Clean sensible object datas
 *
 * @param   object  $toclean    Object to clean
 * @return  array   Array of cleaned object properties
 */
function cleanAgendaEventsDatas($toclean)
{
    unset($toclean->usermod);
    unset($toclean->libelle);
    //unset($toclean->array_options);
    unset($toclean->context);
    unset($toclean->canvas);
    unset($toclean->contact);
    unset($toclean->contact_id);
    unset($toclean->thirdparty);
    unset($toclean->user);
    unset($toclean->origin);
    unset($toclean->origin_id);
    unset($toclean->ref_ext);
    unset($toclean->statut);
    unset($toclean->country);
    unset($toclean->country_id);
    unset($toclean->country_code);
    unset($toclean->barcode_type);
    unset($toclean->barcode_type_code);
    unset($toclean->barcode_type_label);
    unset($toclean->barcode_type_coder);
    unset($toclean->mode_reglement_id);
    unset($toclean->cond_reglement_id);
    unset($toclean->cond_reglement);
    unset($toclean->fk_delivery_address);
    unset($toclean->shipping_method_id);
    unset($toclean->fk_account);
    unset($toclean->total_ht);
    unset($toclean->total_tva);
    unset($toclean->total_localtax1);
    unset($toclean->total_localtax2);
    unset($toclean->total_ttc);
    unset($toclean->fk_incoterms);
    unset($toclean->libelle_incoterms);
    unset($toclean->location_incoterms);
    unset($toclean->name);
    unset($toclean->lastname);
    unset($toclean->firstname);
    unset($toclean->civility_id);
    unset($toclean->contact);
    unset($toclean->societe);

    return $toclean;
}
