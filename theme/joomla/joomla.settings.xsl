<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:import href="joomla.xsl" />
  <xsl:output method="html"/>
  <xsl:template name="content">
    <table class="table table-striped">
      <thead>
        <tr><td colspan="2"><h1><xsl:value-of select="//REPORT/TRANSLATE/SETTINGS_TITLE"/></h1></td></tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <label for="mystat_savedata"><xsl:value-of select="//REPORT/TRANSLATE/SAVE_PERIOD"/></label>
          </td>
          <td>
            <select id="mystat_savedata" onchange="changePeriodSave();" style="width:100%;">
              <option value="31">
                <xsl:if test="//REPORT/PARAMETRS/SAVE_PERIOD = 31">
                  <xsl:attribute name="selected">selected</xsl:attribute>
                </xsl:if>
                <xsl:value-of select="//REPORT/TRANSLATE/MONTH"/>
              </option>
              <option value="90">
                <xsl:if test="//REPORT/PARAMETRS/SAVE_PERIOD = 90">
                  <xsl:attribute name="selected">selected</xsl:attribute>
                </xsl:if>
                <xsl:value-of select="//REPORT/TRANSLATE/QUARTER"/>
              </option>
              <option value="120">
                <xsl:if test="//REPORT/PARAMETRS/SAVE_PERIOD = 120">
                  <xsl:attribute name="selected">selected</xsl:attribute>
                </xsl:if>
                <xsl:value-of select="//REPORT/TRANSLATE/FOUR_MONTH"/>
              </option>
              <option value="183">
                <xsl:if test="//REPORT/PARAMETRS/SAVE_PERIOD = 183">
                  <xsl:attribute name="selected">selected</xsl:attribute>
                </xsl:if>
                <xsl:value-of select="//REPORT/TRANSLATE/HALF_YEAR"/>
              </option>
              <option value="365">
                <xsl:if test="//REPORT/PARAMETRS/SAVE_PERIOD = 365">
                  <xsl:attribute name="selected">selected</xsl:attribute>
                </xsl:if>
                <xsl:value-of select="//REPORT/TRANSLATE/YEAR"/>
              </option>
              <option value="730">
                <xsl:if test="//REPORT/PARAMETRS/SAVE_PERIOD = 730">
                  <xsl:attribute name="selected">selected</xsl:attribute>
                </xsl:if>
                <xsl:value-of select="//REPORT/TRANSLATE/TWO_YEARS"/>
              </option>
            </select>
          </td>
        </tr>
        <tr>
          <td>
            <label><xsl:value-of select="//REPORT/TRANSLATE/NOIP_TITLE"/></label>
          </td>
          <td>
            <button id="mystat_addip_btn" class="btn" style="width:100%;margin-bottom:15px;" onclick="jQuery('#mystat_addip').show();jQuery(this).hide();return false;"><xsl:value-of select="//REPORT/TRANSLATE/NOIP_ADDIP"/></button>
            <div id="mystat_addip" style="display:none;background-color:#eee;padding:15px;border-radius:15px;margin-bottom:15px;">
              <label for="mystat_ip"><xsl:value-of select="//REPORT/TRANSLATE/NOIP_IP"/> (<xsl:value-of select="//REPORT/TRANSLATE/NOIP_YOURIP"/>: <a onclick="jQuery('#mystat_ip').val(jQuery(this).html());return false;"><xsl:value-of select="//REPORT/PARAMETRS/IP"/></a>)</label><br/>
              <input type="text" id="mystat_ip" style="width:100%;"/><br/>
              <label for="mystat_mask"><xsl:value-of select="//REPORT/TRANSLATE/NOIP_MASK"/></label><br/>
              <select id="mystat_mask" style="width:100%;">
                <option value="0">/0 (0.0.0.0)</option>
                <option value="1">/1 (128.0.0.0)</option>
                <option value="2">/2 (192.0.0.0)</option>
                <option value="3">/3 (224.0.0.0)</option>
                <option value="4">/4 (240.0.0.0)</option>
                <option value="5">/5 (248.0.0.0)</option>
                <option value="6">/6 (252.0.0.0)</option>
                <option value="7">/7 (254.0.0.0)</option>
                <option value="8">/8 (255.0.0.0)</option>
                <option value="9">/9 (255.128.0.0)</option>
                <option value="10">/10 (255.192.0.0)</option>
                <option value="11">/11 (255.224.0.0)</option>
                <option value="12">/12 (255.240.0.0)</option>
                <option value="13">/13 (255.248.0.0)</option>
                <option value="14">/14 (255.252.0.0)</option>
                <option value="15">/15 (255.254.0.0)</option>
                <option value="16">/16 (255.255.0.0)</option>
                <option value="17">/17 (255.255.128.0)</option>
                <option value="18">/18 (255.255.192.0)</option>
                <option value="19">/19 (255.255.224.0)</option>
                <option value="20">/20 (255.255.240.0)</option>
                <option value="21">/21 (255.255.248.0)</option>
                <option value="22">/22 (255.255.252.0)</option>
                <option value="23">/23 (255.255.254.0)</option>
                <option value="24">/24 (255.255.255.0)</option>
                <option value="25">/25 (255.255.255.128)</option>
                <option value="26">/26 (255.255.255.192)</option>
                <option value="27">/27 (255.255.255.224)</option>
                <option value="28">/28 (255.255.255.240)</option>
                <option value="29">/29 (255.255.255.248)</option>
                <option value="30">/30 (255.255.255.252)</option>
                <option value="31">/31 (255.255.255.254)</option>
                <option value="32" selected="selected">/32 (255.255.255.255)</option>
              </select><br/>
              <button class="btn btn-success btn-small" onclick="addIp();return false;" style="margin-top:5px;width:100%;text-transform:uppercase;"><xsl:value-of select="//REPORT/TRANSLATE/NOIP_ADD"/></button>
            </div>
            <div id="mystat_iplist" style="margin:5px;"></div>
          </td>
        </tr>
        <tr>
          <td>
            <label for="mystat_role"><xsl:value-of select="//REPORT/TRANSLATE/PLUGINROLE"/></label>
          </td>
          <td>
            <select id="mystat_role" style="width:100%;" onchange="changeRole();">
              <option value="ADMIN">
                <xsl:if test="//REPORT/PARAMETRS/PLUGINROLE = 'ADMIN'">
                  <xsl:attribute name="selected">selected</xsl:attribute>
                </xsl:if>
                <xsl:value-of select="//REPORT/TRANSLATE/PLUGINROLE_ADMIN"/>
              </option>
              <option value="EDITOR">
                <xsl:if test="//REPORT/PARAMETRS/PLUGINROLE = 'EDITOR'">
                  <xsl:attribute name="selected">selected</xsl:attribute>
                </xsl:if>
                <xsl:value-of select="//REPORT/TRANSLATE/PLUGINROLE_EDITOR"/>
              </option>
              <option value="USER">
                <xsl:if test="//REPORT/PARAMETRS/PLUGINROLE = 'USER'">
                  <xsl:attribute name="selected">selected</xsl:attribute>
                </xsl:if>
                <xsl:value-of select="//REPORT/TRANSLATE/PLUGINROLE_USER"/>
              </option>
            </select>
            <div id="mystat_listmenu">
              <xsl:if test="//REPORT/PARAMETRS/PLUGINROLE = 'ADMIN'">
                <xsl:attribute name="style">display:none;</xsl:attribute>
              </xsl:if>
              <xsl:for-each select="//REPORT/MENU">
                <h3 class="hndle"><span><xsl:value-of select="TITLE"/></span></h3>
                <xsl:for-each select="ITEM">
                  <div>
                    <input class="mystat_menuitemlist" type="checkbox" value="{@code}">
                      <xsl:if test="@code = 'settings'">
                        <xsl:attribute name="disabled">disabled</xsl:attribute>
                      </xsl:if>
                      <xsl:if test="@code = //REPORT/DEFAULTREPORT">
                        <xsl:attribute name="disabled">disabled</xsl:attribute>
                        <xsl:attribute name="checked">checked</xsl:attribute>
                      </xsl:if>
                      <xsl:if test="contains(//REPORT/PARAMETRS/PLUGINROLELIST, @code)">
                        <xsl:attribute name="checked">checked</xsl:attribute>
                      </xsl:if>
                    </input>
                    <xsl:value-of select="."/>
                  </div>
                </xsl:for-each>
              </xsl:for-each>
            </div>
          </td>
        </tr>
        <tr>
          <td>
            <label for="mystat_defaultreport"><xsl:value-of select="//REPORT/TRANSLATE/DEFAULT_REPORT"/></label>
          </td>
          <td>
            <select id="mystat_defaultreport" onchange="changeDefaultPage();" style="width:100%;">
              <xsl:for-each select="//REPORT/MENU">
                <optgroup label="{TITLE}">
                  <xsl:for-each select="ITEM">
                    <xsl:if test="not(@code = 'settings')">
                      <option value="{@code}">
                        <xsl:if test="//REPORT/PARAMETRS/DEFAULT_REPORT = @code">
                          <xsl:attribute name="selected">selected</xsl:attribute>
                        </xsl:if>
                        <xsl:value-of select="."/>
                      </option>
                    </xsl:if>
                  </xsl:for-each>
                </optgroup>
              </xsl:for-each>
            </select>
          </td>
        </tr>
      </tbody>
    </table>
    <script type="text/javascript">
      var ip = [];
      <xsl:for-each select="//REPORT/PARAMETRS/IPADRESS/IP">
        ip.push("<xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="."/></xsl:call-template>");
      </xsl:for-each>
    </script>
    <script type="text/javascript"><![CDATA[
      function changePeriodSave(){
        var defval = ]]><xsl:value-of select="//REPORT/PARAMETRS/SAVE_PERIOD"/><![CDATA[
        if(defval>jQuery('#mystat_savedata').val()){
          if(!confirm("]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="//REPORT/TRANSLATE/CONFIRM_CHANGEPERIOD"/></xsl:call-template><![CDATA[")){
            jQuery('#mystat_savedata').val(defval);
          }
        }
      }
      function changeRole(){
        if(jQuery('#mystat_role').val()!='ADMIN'){
          jQuery('#mystat_listmenu').show();
        }else{
          jQuery('#mystat_listmenu').hide();
        }
      }
      function changeDefaultPage(){
        var vl = jQuery('#mystat_defaultreport').val();
        jQuery('.mystat_menuitemlist').each(function(){
          if(jQuery(this).val()!='settings'){
            jQuery(this).prop('disabled', false);
          }
          if(jQuery(this).val()==vl){
            jQuery(this).prop('disabled', true);
            jQuery(this).prop('checked', true);
          }
        });
      }
      function saveSettings(){
        jQuery('#mystat_save').children('.spinner').show();
        var savedata = {};
        savedata.role = jQuery('#mystat_role').val();
        savedata.ip = [];
        if(ip.length>0){
          savedata.ip = ip;
        }
        savedata.period = jQuery('#mystat_savedata').val();
        savedata.rolelist = {};
        jQuery('.mystat_menuitemlist').each(function(){
          savedata.rolelist[jQuery(this).val()] = jQuery(this).is(':checked');
        });
        savedata.defaultreport = jQuery('#mystat_defaultreport').val();
        loadAjax(savedata,function(data){
          document.location.reload();
        });
      }
      function repaintIp(){
        jQuery('#mystat_iplist').html('');
        for(var i=0;i<ip.length;i++){
          var addr = ip[i].split('/');
          jQuery('#mystat_iplist').append('<div style="margin:5px;"><code>'+addr[0]+'</code>/<code>'+addr[1]+'</code><button onclick="delIp('+i+');return false;" class="btn btn-success btn-small" style="height:19px;line-height: 15px;margin-left: 10px;">X</button>'+'</div>');
        }
      }
      function delIp(i){
        if(confirm("]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="//REPORT/TRANSLATE/CONFIRM_DELETEIP"/></xsl:call-template><![CDATA[")){
          ip.splice (i, 1);
          repaintIp();
        }
      }
      function addIp(){
        var ipaddr = jQuery('#mystat_ip').val();
        var ipmask = jQuery('#mystat_mask').val();
        if(/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(ipaddr)){
          jQuery('#mystat_addip_btn').show();
          jQuery('#mystat_addip').hide();
          jQuery('#mystat_ip').val('');
          jQuery('#mystat_mask').val(32);
          if(jQuery.inArray((ipaddr+'/'+ipmask),ip)==-1){
            ip.push(ipaddr+'/'+ipmask);
            repaintIp();
          }
        }else{
          alert("]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="//REPORT/TRANSLATE/ERROR_IP"/></xsl:call-template><![CDATA[");
        }
      }
      jQuery(document).ready(function($){
        $('#mystat_ip').mask('099.099.099.099',{placeholder:'xxx.xxx.xxx.xxx'});
        repaintIp();
      });
    ]]></script>
    <div style="text-align:center;">
      <button class="btn btn-success btn-large" style="width:40%;text-transform:uppercase;" onclick="saveSettings();return false;" id="mystat_save"><span class="icon-apply icon-white"></span> <xsl:value-of select="//REPORT/TRANSLATE/SAVE"/></button>
    </div>
  </xsl:template>
</xsl:stylesheet>