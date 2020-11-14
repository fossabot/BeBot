<?php
/*
* PB_db.php - Display symbiants/pocketboss information. Now using a database table.
*
* BeBot - An Anarchy Online & Age of Conan Chat Automaton
* Copyright (C) 2004 Jonas Jax
* Copyright (C) 2005-2020 J-Soft and the BeBot development team.
*
* Developed by:
* - Alreadythere (RK2)
* - Blondengy (RK1)
* - Blueeagl3 (RK1)
* - Glarawyn (RK1)
* - Khalem (RK1)
* - Naturalistic (RK1)
* - Temar (RK1)
*
* See Credits file for all acknowledgements.
*
*  This program is free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; version 2 of the License only.
*
*  This program is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  You should have received a copy of the GNU General Public License
*  along with this program; if not, write to the Free Software
*  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307
*  USA
*/
$pb = new PB($bot);
class PB extends BaseActiveModule
{
    private $slots
        = array(
            'ocullar' => 'eye',
            'brain' => 'head',
            'right arm' => 'rarm',
            'left arm' => 'larm',
            'right wrist' => 'rwrist',
            'left wrist' => 'lwrist',
            'right hand' => 'rhand',
            'thigh' => 'legs',
            'left hand' => 'lhand'
        );
    private $profs
        = array(
            'adventurer' => 'adv',
            'agent' => 'agent',
            'bureaucrat' => 'crat',
            'doctor' => 'doc',
            'enforcer' => 'enf',
            'engineer' => 'eng',
            'fixer' => 'fixer',
            'keeper' => 'keeper',
            'martial artist' => 'ma',
            'meta-physicist' => 'mp',
            'nano-technician' => 'nt',
            'soldier' => 'sol',
            'trader' => 'trader'
        );
    private $units
        = array(
            'adv' => array(
                'infantry',
                'artillery',
                'support'
            ),
            'agent' => array('artillery'),
            'crat' => array(
                'control',
                'extermination'
            ),
            'doc' => array('support'),
            'enf' => array('infantry'),
            'eng' => array('control'),
            'fixer' => array(
                'artillery',
                'support'
            ),
            'keeper' => array(
                'infantry',
                'support'
            ),
            'ma' => array(
                'infantry',
                'support'
            ),
            'mp' => array(
                'control',
                'extermination',
                'support'
            ),
            'nt' => array('extermination'),
            'sol' => array('artillery'),
            'trader' => array(
                'artillery',
                'control',
                'support'
            )
        );


    function __construct(&$bot)
    {
        parent::__construct($bot, get_class($this));
        $this->register_command("all", "pb", "GUEST");
        $this->help['description'] = "Shows know pocket bosses and their loot.";
        $this->help['command']['pb <name>'] = "Shows the known loot of the pocket boss <name>.";
        $this->help['command']['symb <type> <slot>'] = "Shows all known drops of symbs of <type> in <slot>.";
        $this->help['command']['symb <profession> <slot>'] = "Shows all known drops of symbs usable by <profession> in <slot>";
        $this->help['notes']
            = "Supported slots: eye, ocullar, head, brain, ear, rarm, right arm, chest, larm, left arm, rwrist, right wrist, waist, lwrist, left wrist, rhand, right hand, legs, thigh, lhand, left hand, feet.<br>";
        $this->help['notes'] .= "Supported type: support, control, infantry, artillery, extermination.";
        $this->help['notes']
            .= "Supported professions: adventurer, adv, agent, bureaucrat, crat, doctor, doc, enforcer, enf, engineer, eng, fixer, martial artist, ma, meta-Physicist, mp, nano-technician, nt, soldier, sol, keeper, trader<br>";
        $this->help['notes'] .= "For this module to work you need to have run pb_data.php in the 'extras' folder!";
        $this->tables();
    }


    function tables()
    {
        $this->bot->db->define_tablename("pocketbosses", "true");
        Switch ($this->bot->db->get_version("pocketbosses")) {
            case 1:
            case 2:
                $filename = "./Extras/Symbiants/PocketBosses.sql";
                $handle = fopen($filename, "r");
                $query = fread($handle, filesize($filename));
                fclose($handle);
                $query = explode(";", $query);
                foreach ($query as $q) {
                    if (!empty($q)) {
                        $this->bot->db->query($q);
                    }
                }
        }
        $this->bot->db->set_version("pocketbosses", 3);
    }


    function command_handler($name, $msg, $origin)
    {
        $com = $this->parse_com(
            $msg,
            array(
                 'com',
                 'args'
            )
        );
        switch ($com['com']) {
            case 'pb':
                switch ($com['args']) {
                    case '':
                        return $this->list_all($name);
                        break;
                    default:
                        return $this->SearchPB($com['args']);
                        break;
                }
                break;
            Default:
                Return "##error##Error: Unknown Command ##highlight##" . $com['com'] . "##end## in PB module##end##";
        }
    }


    function CreateBlob($pb, $symbs)
    {
        $msg = "##lightyellow##::::: Remains of " . $pb[1] . " :::::##end##\n\n";
        $msg .= "##normal##";
        $msg .= "##highlight##Location: ##end##" . $pb[4] . "\n";
        $msg .= "##highlight##Found on: ##end##" . $pb[5] . "\n";
        $msg .= "##highlight##Mob Level: ##end##" . $pb[2] . "\n";
        $msg .= "##highlight##General Location: ##end##" . $pb[3] . "\n";
        $msg .= "____________________________________  ##end##\n";
        asort($symbs);
        foreach ($symbs as $symb) {
            $title = "QL {$symb[0]} {$symb[3]} " . array_search(
                    $symb[1],
                    $this->slots
                ) . " symbiant, {$symb[2]} unit aban";
            $title = ucwords($title);
            $msg .= $this->bot->core("tools")
                    ->make_item($symb[4], $symb[4], $symb[0], $title, true) . "\n";
        }
        return $this->bot->core("tools")
            ->make_blob("Remains of " . $pb[1], $msg);
    }


    function best_match($search)
    {
        $haystack = $this->bot->db->select("SELECT name FROM pocketbosses");
        // 1st lets check for pocketbosses that contain the string
        foreach ($haystack as $straw) {
            //echo "is $search in ".$straw[0]."\n";
            if (stristr($straw[0], $search)) {
                $smallerhaystack[] = $straw;
            }
        }
        if (empty($smallerhaystack)) {
            $smallerhaystack = $haystack;
        }
        foreach ($smallerhaystack as $straw) {
            $distance[levenshtein($search, $straw[0])] = $straw[0];
        }
        ksort($distance);
        return (array_shift($distance));
    }

    function SearchPB($search)
    {
        $boss = $this->best_match($search);
        if ($boss === false) {
            return ("I found no pocket boss like '$search'");
        }
        $query = "SELECT ID, name, level, Playfield, Place, pattern_mobs FROM pocketbosses WHERE name LIKE '$boss'";
        $boss = $this->bot->db->select($query);
        if (empty($boss)) {
            return 'Could not find the Pocketboss "##highlight##' . $search . '##end##"';
        }
        return $this->CreateBlob($boss[0], $this->get_symbs($boss[0][0]));
    }


    function get_symbs($id)
    {
        $symbs = $this->bot->db->select("SELECT QL, slot, unit, Name, itemref FROM symbiants WHERE boss_id = $id");
        Return $symbs;
    }


    function list_all($name)
    {
        $query = "SELECT Playfield, name FROM pocketbosses ORDER BY Playfield, level, name";
        $bosslist = $this->bot->db->select($query);
        $window = "##blob_title## :::  Pocket Bosses  :::##end####blob_text##\n";
        $area = false;
        foreach ($bosslist as $boss) {
            if ($boss[0] !== $area) {
                $area = $boss[0];
                $window .= "\n##blob_title##  :: " . $boss[0] . " ::##end##\n";
            }
            $window .= "&#8226; " . $this->bot->core("tools")
                    ->chatcmd("pb " . $boss[1], $boss[1]) . "\n";
        }
        return ("Listing all " . $this->bot->core("tools")
                ->make_blob("Pocket Bosses", $window));
    }
}

?>
