<?php
/*
    All Emoncms code is released under the GNU Affero General Public License.
    See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
*/
// no direct access
defined('EMONCMS_EXEC') or die('Restricted access');
global $path; $v=4;
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="<?php echo $path; ?>Modules/user/profile/profile.css?v=<?php echo $v; ?>" rel="stylesheet">
<script type="text/javascript" src="<?php echo $path; ?>Modules/user/profile/md5.js?v=<?php echo $v; ?>"></script>
<script type="text/javascript" src="<?php echo $path; ?>Lib/misc/qrcode.js?v=<?php echo $v; ?>"></script>
<script type="text/javascript" src="<?php echo $path; ?>Lib/misc/clipboard.js?v=<?php echo $v; ?>"></script>
<script type="text/javascript" src="<?php echo $path; ?>Modules/user/user.js?v=<?php echo $v; ?>"></script>
<script src="<?php echo $path; ?>Lib/vue.min.js"></script>

<div id="app" v-cloak class="container mt-4">

  <h3 class="mb-3"><?php echo tr('My Account'); ?></h3>

  <div class="table-responsive">
  <table class="table align-middle">
    <tr>
      <td class="text-muted"><?php echo tr('User ID'); ?></td>    
      <td>{{ user.id }}</td>
      <td></td>
      <td>
        <button class="btn btn-danger btn-sm mt-2" @click="delete_account()">
          <?php echo tr('Delete account'); ?>
        </button>
      </td>
    </tr>

    <tr>
      <td class="text-muted"><?php echo tr('Username'); ?></td>
      <td>
        <span v-if="!edit.username">{{ user.username }}</span>
        <div v-else class="d-flex gap-2">
          <input type="text" class="form-control form-control-sm" v-model="user.username"/>
          <button class="btn btn-primary btn-sm" @click="save_username(user.username)"><i class="bi bi-check"></i></button>
        </div>
      </td>
      <td><i class="icon-pencil" v-if="!edit.username" @click="show_edit('username')"></i></td>
      <td></td>
    </tr>

    <tr>
      <td class="text-muted"><?php echo tr('Email'); ?></td>
      <td>
        <span v-if="!edit.email">{{ user.email }}</span>
        <div v-else class="d-flex gap-2">
          <input type="text" class="form-control form-control-sm" v-model="user.email"/>
          <button class="btn btn-primary btn-sm" @click="save_email(user.email)"><i class="bi bi-check"></i></button>
        </div>
      </td>
      <td><i class="icon-pencil" v-if="!edit.email" @click="show_edit('email')"></i></td>
      <td></td>
    </tr>

    <tr>
      <td class="text-muted"><?php echo tr('Read & Write API Key'); ?></td>
      <td><div class="apikey">{{ user.apikey_write }}</div></td>
      <td><i class="icon-share" @click="copy_text_to_clipboard(user.apikey_write,'<?php echo addslashes(tr("Write API Key copied to clipboard")); ?>')"></i></td>
      <td>
        <button class="btn btn-secondary btn-sm mt-2" @click="new_apikey('write')">
            <?php echo tr('Generate New'); ?>
        </button>
      </td>
    </tr>

    <tr>
      <td class="text-muted"><?php echo tr('Read Only API Key'); ?></td>
      <td><div class="apikey">{{ user.apikey_read }}</div></td>
      <td><i class="icon-share" @click="copy_text_to_clipboard(user.apikey_read,'<?php echo addslashes(tr("Read API Key copied to clipboard")); ?>')"></i></td>
      <td>
        <button class="btn btn-secondary btn-sm mt-2" @click="new_apikey('read')">
            <?php echo tr('Generate New'); ?>
        </button>
      </td>
    </tr>

    <tr>
      <td class="text-muted"><?php echo tr('Password'); ?></td>    
      <td>
        <span v-if="!edit.password" class="text-muted">**********</span>
        <div v-else class="d-flex flex-column gap-3">

          <div>
            <label class="text-muted"><?php echo tr('Current password'); ?></label>
            <input type="password" class="form-control form-control-sm" v-model="password.current" />
          </div>

          <div>
            <label class="text-muted"><?php echo tr('New password'); ?></label>
            <input type="password" class="form-control form-control-sm" v-model="password.new" />
          </div>

          <div>
            <label class="text-muted"><?php echo tr('Repeat new password'); ?></label>
            <input type="password" class="form-control form-control-sm" v-model="password.repeat" />
          </div>

          <div class="d-flex gap-2">
            <button class="btn btn-primary btn-sm" @click="change_password()"><?php echo tr('Save'); ?></button>
            <button class="btn btn-secondary btn-sm" @click="edit.password=false"><?php echo tr('Cancel'); ?></button>
          </div>
        </div>
      </td>
      <td><i class="icon-pencil" v-if="!edit.password" @click="show_edit('password')"></i></td>
      <td></td>
    </tr>

  </table>
  </div>

  <h3 class="mt-4 mb-3"><?php echo tr('Profile'); ?></h3>

  <div class="table-responsive">
  <table class="table align-middle">
    <tr>
      <td class="text-muted"><?php echo tr('Gravatar'); ?></td>
      <td>
        <img v-if="!edit.gravatar" class="border p-1" :src="'https://www.gravatar.com/avatar/'+CryptoJS.MD5(user.gravatar)" />      
        <div v-else class="d-flex gap-2">
          <input type="text" class="form-control form-control-sm" v-model="user.gravatar" />
          <button class="btn btn-primary btn-sm" @click="save('gravatar')"><i class="bi bi-check"></i></button>
        </div>
      </td>
      <td><i class="icon-pencil" v-if="!edit.gravatar" @click="show_edit('gravatar')"></i></td>
    </tr>

    <tr>
      <td class="text-muted"><?php echo tr('Name'); ?></td>
      <td>
        <span v-if="!edit.name">{{ user.name }}</span>
        <div v-else class="d-flex gap-2">
          <input type="text" class="form-control form-control-sm" v-model="user.name"/>
          <button class="btn btn-primary btn-sm" @click="save('name')"><i class="bi bi-check"></i></button>
        </div>
      </td>
      <td><i class="icon-pencil" v-if="!edit.name" @click="show_edit('name')"></i></td>
    </tr>

    <tr>
      <td class="text-muted"><?php echo tr('Location'); ?></td>
      <td>
        <span v-if="!edit.location">{{ user.location }}</span>
        <div v-else class="d-flex gap-2">
          <input type="text" class="form-control form-control-sm" v-model="user.location"/>
          <button class="btn btn-primary btn-sm" @click="save('location')"><i class="bi bi-check"></i></button>
        </div>
      </td>
      <td><i class="icon-pencil" v-if="!edit.location" @click="show_edit('location')"></i></td>
    </tr>

    <tr>
      <td class="text-muted"><?php echo tr('Timezone'); ?></td>
      <td>
        <span v-if="!edit.timezone">{{ user.timezone }}</span>
        <div v-else class="d-flex gap-2">
          <select class="form-select form-select-sm" v-model="user.timezone">
            <option v-for="tz in timezones" :value="tz.id">{{ tz.id }} {{ tz.gmt_offset_text }}</option>
          </select>
          <button class="btn btn-primary btn-sm" @click="save('timezone')"><i class="bi bi-check"></i></button>
        </div>
      </td>
      <td><i class="icon-pencil" v-if="!edit.timezone" @click="show_edit('timezone')"></i></td>
    </tr>

    <tr>
      <td class="text-muted"><?php echo tr('Language'); ?></td>
      <td>
        <span v-if="!edit.language">{{ languages[user.language] }}</span> 
        <span class="text-muted ms-3" v-if="!edit.language && translation_status[user.language]!=undefined">
          <?php echo tr("Translation: "); ?>{{ translation_status[user.language].prc_complete }}% <?php echo tr("complete"); ?>
        </span>

        <div v-if="edit.language" class="d-flex gap-2">
          <select class="form-select form-select-sm" v-model="user.language">
            <option value="en_GB" selected>English (United Kingdom)</option>
            <option v-for="(name,code) in languages" :value="code" v-if="code!='en_GB'">{{ name }}</option>
          </select>
          <button class="btn btn-primary btn-sm" @click="save('language')"><i class="bi bi-check"></i></button>
        </div>
      </td>
      <td><i class="icon-pencil" v-if="!edit.language" @click="show_edit('language')"></i></td>
    </tr>

    <tr>
      <td class="text-muted"><?php echo tr('Starting page'); ?></td>
      <td>
        <span v-if="!edit.startingpage">{{ user.startingpage }}</span>
        <div v-else class="d-flex gap-2">
          <input type="text" class="form-control form-control-sm" v-model="user.startingpage"/>
          <button class="btn btn-primary btn-sm" @click="save('startingpage')"><i class="bi bi-check"></i></button>
        </div>
      </td>
      <td><i class="icon-pencil" v-if="!edit.startingpage" @click="show_edit('startingpage')"></i></td>
    </tr>

  </table>
  </div>

</div> <!-- Vue section end -->


<script>
var languages = <?php echo json_encode(get_available_languages_with_names()); ?>;
var translation_status = <?php echo json_encode(get_translation_status()); ?>;
var str_passwords_do_not_match = "<?php echo tr('Passwords do not match'); ?>";
</script>

<script type="text/javascript" src="<?php echo $path; ?>Modules/user/profile/profile.js?v=<?php echo $v; ?>"></script>
