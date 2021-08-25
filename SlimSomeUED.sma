#include <amxmodx>

new const USERS_FILE[] = "addons/amxmodx/configs/users.ini";

new Trie: g_trie;

new g_iAdminExpired[MAX_PLAYERS + 1];

public plugin_init() {
  register_plugin("SlimSome CMS: User Expiration Date", "1.0", "szawesome");
  
  g_trie = TrieCreate();

  CheckDays();
  GetDays();
}

public plugin_end() {
  TrieDestroy(g_trie);
}

public client_authorized(id) {
  g_iAdminExpired[id] = GetTrieParsedTime(id);
}

public plugin_natives() {
  register_native("admin_expired", "admin_expired_callback", true);
}

public admin_expired_callback(id) {
  return g_iAdminExpired[id];
}
  
CheckDays() {
  new file = fopen(USERS_FILE, "r+");

  if(!file) return 0;

  new buffer[256], curr_line;
  new nickname[64], password[64], flags[32], access[32], exp_date[32];
  
  while(!feof(file)) {
    fgets(file, buffer, charsmax(buffer));
    trim(buffer);

    curr_line++;

    if(!buffer[0]) {
      continue;
    }

    if(buffer[0] != '^"') {
      continue;
    }

    parse(buffer, 
      nickname, charsmax(nickname),
      password, charsmax(password),
      flags, charsmax(flags),
      access, charsmax(access),
      exp_date, charsmax(exp_date));

    if(str_to_num(exp_date) == 0 || str_to_num(exp_date) > get_systime()) {
      formatex(buffer, charsmax(buffer), "^"%s^" ^"%s^" ^"%s^" ^"%s^" ^"%s^"", nickname, password, flags, access, exp_date);
    } else {
      formatex(buffer, charsmax(buffer), ";^"%s^" ^"%s^" ^"%s^" ^"%s^" ^"%s^"", nickname, password, flags, access, exp_date);
    }

    write_file(USERS_FILE, buffer, curr_line-1);
  }
  
  // server_cmd("amx_reloadadmins"); - если плагин поставить выше admin.amxx тогда можно и не посылать эту команду
  return fclose(file);
}

GetDays() {
  new file = fopen(USERS_FILE, "r");

  if(!file) return 0;

  new buffer[256], admin_id[32], exp_date[32];
  
  while(!feof(file)) {
    fgets(file, buffer, charsmax(buffer));
    trim(buffer);

    if(!buffer[0] || buffer[0] != '^"')
      continue;
      
    for(new i; i<5;i++) {
      if(strlen(buffer) <= 0)
        break;
      if(!i) {
        argbreak(buffer, admin_id, charsmax(admin_id), buffer, charsmax(buffer));
      } else {
        argbreak(buffer, exp_date, charsmax(exp_date), buffer, charsmax(buffer));
      }
    }
    
    TrieSetCell(g_trie, admin_id, str_to_num(exp_date) == 0 ? 0 : str_to_num(exp_date));
  }
  
  return fclose(file);
}

stock GetTrieParsedTime(id) {
  static _id[32], cell;

  get_user_name(id, _id, charsmax(_id));
  
  if(TrieGetCell(g_trie, _id, cell)) {
    return cell;
  }

  get_user_authid(id, _id, charsmax(_id));

  if(TrieGetCell(g_trie, _id, cell)) {
    return cell;
  }
    
  get_user_ip(id,  _id, charsmax(_id), 1);

  if(TrieGetCell(g_trie, _id, cell)) {
    return cell;
  }

  return -1;
}
