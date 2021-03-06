<?php
/*
Copyright 2020 whatever127

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/

$updateId = isset($_GET['id']) ? $_GET['id'] : 0;

require_once 'api/listlangs.php';
require_once 'api/updateinfo.php';
require_once 'shared/style.php';

if(!$updateId) {
    fancyError('UNSPECIFIED_UPDATE', 'downloads');
    die();
}

if(!checkUpdateIdValidity($updateId)) {
    fancyError('INCORRECT_ID', 'downloads');
    die();
}

$updateInfo = uupUpdateInfo($updateId);
$updateInfo = isset($updateInfo['info']) ? $updateInfo['info'] : array();

if(!isset($updateInfo['title'])) {
    $updateTitle = 'Unknown update: '.$updateId;
} else {
    $updateTitle = $updateInfo['title'];
}

if(!isset($updateInfo['arch'])) {
    $updateArch = '';
} else {
    $updateArch = $updateInfo['arch'];
}

if(!isset($updateInfo['build'])) {
    $build = $s['unknown'];
} else {
    $build = $updateInfo['build'];
}

if(!isset($updateInfo['ring'])) {
    $ring = null;
} else {
    $ring = $updateInfo['ring'];
}

if(!isset($updateInfo['flight'])) {
    $flight = null;
} else {
    $flight = $updateInfo['flight'];
}

if(!isset($updateInfo['created'])) {
    $created = null;
} else {
    $created = $updateInfo['created'];
}

$updateTitle = $updateTitle.' '.$updateArch;

$langs = uupListLangs($updateId);
$langsTemp = array();

foreach($langs['langList'] as $lang) {
    if(isset($s["lang_$lang"])) {
        $langsTemp[$lang] = $s["lang_$lang"];
    } else {
        $langsTemp[$lang] = $langs['fancyLangNames'][$lang];
    }
}

$langs = $langsTemp;
unset($langsTemp);
locasort($langs, $s['code']);

if(isset($updateInfo['containsCU']) && $updateInfo['containsCU'] = 1) {
    $containsCU = 1;
} else {
    $containsCU = 0;
}

if(in_array(strtolower($s['code']), array_keys($langs))) {
    $defaultLang = strtolower($s['code']);
} else {
    $defaultLang = 'en-us';
}

//Set fancy name for ring and flight of build
if($ring == 'WIF' && $flight == 'Skip') {
    $fancyRingName = 'Skip Ahead';
} elseif($ring == 'WIF' && $flight == 'Active') {
    $fancyRingName = 'Insider Fast';
} elseif($ring == 'WIS' && $flight == 'Active') {
    $fancyRingName = 'Insider Slow';
} elseif($ring == 'RP' && $flight == 'Current') {
    $fancyRingName = 'Release Preview';
} elseif($ring == 'RETAIL') {
    $fancyRingName = 'Retail';
} else {
    if($ring && $flight) {
        $fancyRingName = "$ring, $flight";
    } elseif($ring) {
        $fancyRingName = "$ring";
    } else {
        $fancyRingName = $s['unknown'];
    }
}

$findFilesUrl = "./findfiles.php?id=".htmlentities($updateId);

styleUpper('downloads', sprintf($s['selectLangFor'], $updateTitle));
?>

<h3 class="ui centered header">
    <div class="content">
        <i class="fitted cubes icon"></i>&nbsp;
        <?php echo htmlentities($updateTitle); ?>
    </div>
</h3>

<?php
if(!file_exists('packs/'.$updateId.'.json.gz')) {
    styleNoPackWarn();
}

if($updateArch == 'arm64') {
    styleCluelessUserArm64Warn();
}
?>

<div class="ui two columns mobile stackable centered grid">
    <div class="column">
        <h3 class="ui header">
            <i class="globe icon"></i>
            <div class="content">
                <?php echo $s['chooseLang']; ?>
                <div class="sub header"><?php echo $s['chooseLangDesc']; ?></div>
            </div>
        </h3>

<?php
if(count($langs) > 0) {
    echo <<<EOD
<form class="ui form" action="./selectedition.php" method="get" id="langForm">
    <input type="hidden" name="id" value="$updateId">
    <div class="field">
        <label>{$s['lang']}</label>
        <select class="ui search dropdown" name="pack">
EOD;

    foreach($langs as $key => $val) {
        if($key == $defaultLang) {
            echo '<option value="'.$key.'" selected>'.$val."</option>\n";
        } else {
            echo '<option value="'.$key.'">'.$val."</option>\n";
        }
    }

    echo <<<EOD
        </select>
    </div>

    <button class="ui fluid right labeled icon blue button" id="submitForm" type="submit">
        <i class="right arrow icon"></i>
        {$s['next']}
    </button>
</form>

<div class="ui info message">
    <i class="info icon"></i>
    {$s['selectLangInfoText1']}
</div>

EOD;
} else {
    echo <<<EOD
<div class="ui center aligned one column padded relaxed grid">
    <div class="row">
        <div class="column">
            <i class="huge info icon"></i>
        </div>
    </div>
    <div class="row">
        <div class="column">
            <p>{$s['noLangsAvailable']}</p>
        </div>
    </div>
</div>

EOD;
}
?>
    </div>

    <div class="column">
        <h3 class="ui header">
            <i class="open folder icon"></i>
            <div class="content">
                <?php echo $s['browseFiles']; ?>
                <div class="sub header"><?php echo $s['browseFilesDesc']; ?></div>
            </div>
        </h3>

        <form class="ui form" action="./findfiles.php" method="get">
            <div class="field">
                <label><?php echo $s['searchFiles']; ?></label>
                <div class="ui action input">
                    <input type="hidden" name="id" value="<?php echo htmlentities($updateId); ?>">
                    <input type="text" name="q" placeholder="<?php echo $s['searchForFiles']; ?>">
                    <button class="ui blue icon button" type="submit"><i class="search icon"></i></button>
                </div>
            </div>
        </form>

        <a class="ui fluid right labeled icon button"
        href="<?php echo $findFilesUrl; ?>" style="margin-top:1rem;">
            <i class="open folder icon"></i>
            <?php echo $s['allFiles']; ?>
        </a>

        <div class="ui positive message">
            <i class="paper plane icon"></i>
<?php
printf(
    $s['toSearchForCUUseQuery'],
    "<a href=\"$findFilesUrl&q=Windows10 KB\">Windows10 KB</a>"
);
?>
        </div>
    </div>
</div>

<div class="ui fluid tiny three steps">
      <div class="active step">
            <i class="world icon"></i>
            <div class="content">
                <div class="title"><?php echo $s['chooseLang']; ?></div>
                <div class="description"><?php echo $s['chooseLangDesc']; ?></div>
            </div>
      </div>

      <div class="step">
            <i class="archive icon"></i>
            <div class="content">
                <div class="title"><?php echo $s['chooseEdition']; ?></div>
                <div class="description"><?php echo $s['chooseEditionDesc']; ?></div>
            </div>
      </div>

      <div class="step">
            <i class="briefcase icon"></i>
            <div class="content">
                <div class="title"><?php echo $s['summary']; ?></div>
                <div class="description"><?php echo $s['summaryDesc']; ?></div>
            </div>
      </div>
</div>

<h4 class="ui horizontal divider">
    <?php echo $s['information']; ?>
</h4>

<div class="ui three columns mobile stackable centered grid" style="margin-top: 1em;">
    <div class="column">
        <h4 class="ui center aligned tiny icon header">
            <i class="archive icon"></i>
            <div class="content">
                <?php echo $s['build']; ?>
                <div class="sub header"><?php echo $build; ?></div>
            </div>
        </h4>
    </div>
    <div class="column">
        <h4 class="ui center aligned tiny icon header">
            <i class="cogs icon"></i>
            <div class="content">
                <?php echo $s['ring']; ?>
                <div class="sub header"><?php echo $fancyRingName; ?></div>
            </div>
        </h4>
    </div>
    <div class="column">
        <h4 class="ui center aligned tiny icon header">
            <i class="calendar icon"></i>
            <div class="content">
                <?php echo $s['dateAdded']; ?>
                <div class="sub header">
<?php
if($created == null) {
    echo $s['unknown'];
} else {
    echo date("Y-m-d H:i:s T", $created);
}
?>
                </div>
            </div>
        </h4>
    </div>
</div>

<script>
    $('select.dropdown').dropdown();
    $('.ui.radio.checkbox').checkbox();
</script>

<?php
styleLower();
?>
