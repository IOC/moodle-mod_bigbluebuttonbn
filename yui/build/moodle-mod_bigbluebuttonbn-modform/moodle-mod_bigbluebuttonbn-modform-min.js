YUI.add("moodle-mod_bigbluebuttonbn-modform",function(e,t){M.mod_bigbluebuttonbn=M.mod_bigbluebuttonbn||{},M.mod_bigbluebuttonbn.modform={bigbluebuttonbn:{},strings:{},init:function(e){this.bigbluebuttonbn=e,this.strings={as:M.str.bigbluebuttonbn.mod_form_field_participant_list_text_as,viewer:M.str.bigbluebuttonbn.mod_form_field_participant_bbb_role_viewer,moderator:M.str.bigbluebuttonbn.mod_form_field_participant_bbb_role_moderator,remove:M.str.bigbluebuttonbn.mod_form_field_participant_list_action_removee},this.update_instance_type_profile(),this.participant_list_init()},update_instance_type_profile:function(){var t=e.one("#id_type");this.apply_instance_type_profile(this.bigbluebuttonbn.instance_type_profiles[t.get("value")])},apply_instance_type_profile:function(e){var t=e.features,n=t.includes("all");this.show_fieldset("id_room",n||t.includes("showroom")),this.show_fieldset("id_recordings",n||t.includes("showrecordings")),this.show_input("id_recordings_imported",t.includes("showrecordings")),this.show_fieldset("id_preuploadpresentation",n||t.includes("preuploadpresentation")),this.show_fieldset("id_permissions",n||t.includes("permissions")),this.show_fieldset("id_schedule",n||t.includes("schedule"))},show_fieldset:function(t,n){var r=e.DOM.byId(t);if(!r)return;if(n){e.DOM.setStyle(r,"display","block");return}e.DOM.setStyle(r,"display","none")},show_input:function(t,n){var r=e.DOM.byId(t);if(!r)return;var i=e.one(r).ancestor("div").ancestor("div");if(n){i.setStyle("display","block");return}i.setStyle("display","none")},participant_selection_set:function(){this.select_clear("bigbluebuttonbn_participant_selection");var e=document.getElementById("bigbluebuttonbn_participant_selection_type");for(var t=0;t<e.options.length;t++)if(e.options[t].selected){var n=this.bigbluebuttonbn.participant_data[e.options[t].value].children;for(var r in n)n.hasOwnProperty(r)&&this.select_add_option("bigbluebuttonbn_participant_selection",n[r].name,n[r].id);e.options[t].value==="all"?(this.select_add_option("bigbluebuttonbn_participant_selection","---------------","all"),this.select_disable("bigbluebuttonbn_participant_selection")):this.select_enable("bigbluebuttonbn_participant_selection")}},participant_list_init:function(){var e,t,n,r;for(var i=0;i<this.bigbluebuttonbn.participant_list.length;i++){e=this.bigbluebuttonbn.participant_list[i].selectiontype,t=this.bigbluebuttonbn.participant_list[i].selectionid,n=this.bigbluebuttonbn.participant_list[i].role,r=this.bigbluebuttonbn.participant_data[e];if(e!="all"&&typeof r.children[t]=="undefined"){this.participant_remove_from_memory(e,t);continue}this.participant_add_to_form(e,t,n)}this.participant_list_update()},participant_list_update:function(){var e=document.getElementsByName("participants")[0];e.value=JSON.stringify(this.bigbluebuttonbn.participant_list).replace(/"/g,"&quot;")},participant_remove:function(e,t){this.participant_remove_from_memory(e,t),this.participant_remove_from_form(e,t),this.participant_list_update()},participant_remove_from_memory:function(e,t){var n=t===""?null:t;for(var r=0;r<this.bigbluebuttonbn.participant_list.length;r++)this.bigbluebuttonbn.participant_list[r].selectiontype==e&&this.bigbluebuttonbn.participant_list[r].selectionid==n&&this.bigbluebuttonbn.participant_list.splice(r,1)},participant_remove_from_form:function(e,t){var n="participant_list_tr_"+e+"-"+t,r=document.getElementById("participant_list_table");for(var i=0;i<r.rows.length;i++)r.rows[i].id==n&&r.deleteRow(i)},participant_add:function(){var e=document.getElementById("bigbluebuttonbn_participant_selection_type"),t=document.getElementById("bigbluebuttonbn_participant_selection");for(var n=0;n<this.bigbluebuttonbn.participant_list.length;n++)if(this.bigbluebuttonbn.participant_list[n].selectiontype==e.value&&this.bigbluebuttonbn.participant_list[n].selectionid==t.value)return;this.participant_add_to_memory(e.value,t.value),this.participant_add_to_form(e.value,t.value,"viewer"),this.participant_list_update()},participant_add_to_memory:function(e,t){this.bigbluebuttonbn.participant_list.push({selectiontype:e,selectionid:t,role:"viewer"})},participant_add_to_form:function(e,t,n){var r,i,s,o,u,a,f,l,c,h,p;r=document.getElementById("participant_list_table"),f=r.insertRow(r.rows.length),f.id="participant_list_tr_"+e+"-"+t,l=f.insertCell(0),l.width="125px",l.innerHTML="<b><i>"+this.bigbluebuttonbn.participant_data[e].name,l.innerHTML+=(e!=="all"?":&nbsp;":"")+"</i></b>",c=f.insertCell(1),c.innerHTML="",e!=="all"&&(c.innerHTML=this.bigbluebuttonbn.participant_data[e].children[t].name),i="&nbsp;<i>"+this.strings.as+"</i>&nbsp;",i+='<select id="participant_list_role_'+e+"-"+t+'"',i+=" onchange=\"M.mod_bigbluebuttonbn.modform.participant_list_role_update('",i+=e+"', '"+t,i+='\'); return 0;" class="select custom-select">',u=["viewer","moderator"];for(a=0;a<u.length;a++)s="",u[a]===n&&(s=' selected="selected"'),i+='<option value="'+u[a]+'"'+s+">"+this.strings[u[a]]+"</option>";i+="</select>",h=f.insertCell(2),h.innerHTML=i,p=f.insertCell(3),p.width="20px",o="x",this.bigbluebuttonbn.icons_enabled&&(o=this.bigbluebuttonbn.pix_icon_delete),i='<a class="btn btn-link" onclick="M.mod_bigbluebuttonbn.modform.participant_remove(\'',i+=e+"', '"+t,i+='\'); return 0;" title="'+this.strings.remove+'">'+o+"</a>",p.innerHTML=i},participant_list_role_update:function(e,t){var n=document.getElementById("participant_list_role_"+e+"-"+t);for(var r=0;r<this.bigbluebuttonbn.participant_list.length;r++)this.bigbluebuttonbn.participant_list[r].selectiontype==e&&this.bigbluebuttonbn.participant_list[r].selectionid==(t===""?null:t)&&(this.bigbluebuttonbn.participant_list[r].role=n.value);this.participant_list_update()},select_clear:function(e){var t=document.getElementById(e);while(t.length>0)t.remove(t.length-1)},select_enable:function(e){var t=document.getElementById(e);t.disabled=!1},select_disable:function(e){var t=document.getElementById(e);t.disabled=!0},select_add_option:function(e,t,n){var r=document
.getElementById(e),i=document.createElement("option");i.text=t,i.value=n,r.add(i,i.length)}}},"@VERSION@",{requires:["base","node"]});
