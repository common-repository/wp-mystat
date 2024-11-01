<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:import href="../template.xsl" />
  <xsl:output method="html"/>
  <xsl:template match="/">
    <style type="text/css">
      body{cursor:default;}
      .wrap #mystat_graphic{margin-bottom:10px;padding:20px;}
      .wrap .notice.notice-warning{margin-left: 125px;}
      .wrap .spinner{visibility: visible;display: none;margin: 3px 10px 0;}
      .wrap .spinner.inline{float: none;margin: -6px 10px;}
      .wrap .button{white-space:normal;}
      .wrap .wp-filter{margin:0;margin-bottom:10px;}
      .wrap .wp-filter .period{padding:10px 0;line-height:27px;}
      .wrap .wp-filter .period .button{white-space:normal;}
      .wrap .wp-filter .period .text{line-height:28px;margin-right:20px;}
      .wrap .pressthis,.wrap .pressthis-bookmarklet{margin: 20px 0;margin-left:125px;}
      .wrap .pressthis a, .wrap .pressthis-bookmarklet a{cursor:default;}
      .wrap .pressthis a:after, .wrap .pressthis-bookmarklet:after{width: 130px;}
      .wrap .postbox-container .postbox{min-width: 197px;}
      .wrap .postbox-container .hndle{cursor:pointer;}
      .wrap .postbox-container .inside .button{width:100%;}
      .wrap .progress{height: 10px;width: 100%;line-height: 2em;padding: 0;overflow: hidden;border-radius: 22px;background: #DDD;box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);}
      .wrap .progress .percent{position: relative;width: 100%;padding: 0;font-size: 9px;color: #FFF;text-align: center;line-height: 11px;font-weight: 400;text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);}
      .wrap .progress .bar{width: 0;height: 100%;margin-top: -11px;border-radius: 22px;background-color: #0074A2;box-shadow: inset 0 0 2px rgba(0, 0, 0, 0.3);}
      .wrap #dashboard-widgets{margin-top:10px;}
      .wrap #dashboard-widgets .postbox-container:first-child{margin-right: 1%;}
      .wrap #dashboard-widgets .postbox-container .postbox{padding: 15px;}
      .wrap #logo{position:absolute;left:0px;width:115px;height:115px;}
      .wrap .maintitle{margin-left:125px;}
      .wrap .centerblock{margin:20px auto;text-align:center;}
      .wrap .ortag{margin: 0px 10px;top: 4px;position: relative;}
      .wrap #uuidcontainer{margin-top:30px;}
      .wrap #uuidcode{width:300px;height:23px;}
      .wrap .domainselect{margin-bottom:10px;}
      .wrap select{width:350px;}
      .wrap .button-primary{position: relative;}
      .wrap .button-primary .spinner{position:absolute;right: -35px;top:-2px;}
      .date-picker-wrapper .drp_top-bar .apply-btn{background: #2EA2CC;border-color: #0074A2;-webkit-box-shadow: inset 0 1px 0 rgba(120, 200, 230, 0.5),0 1px 0 rgba(0, 0, 0, 0.15);box-shadow: inset 0 1px 0 rgba(120, 200, 230, 0.5),0 1px 0 rgba(0, 0, 0, 0.15);color: #FFF;text-decoration: none;}
    </style>
    <div class="wrap">
      <div id="poststuff">
        <div id="post-body" class="columns-2">
          <div id="post-body-content" style="position: relative;">
            <div class="wp-filter">
              <div class="period"><span class="text"><xsl:value-of select="//REPORT/TRANSLATE/PERIODREPORT"/></span> <a class="button" data-range="{//REPORT/PERIOD/START} - {//REPORT/PERIOD/END}" id="dataselectrange"><span class="data"><xsl:value-of select="//REPORT/PERIOD/START"/> - <xsl:value-of select="//REPORT/PERIOD/END"/></span> <span class="spinner"></span></a></div>
            </div>
            <div class="wp-filter">
              <div id="center" class="centerblock"></div>
              <script type="text/javascript">logoSVG.setSize(256).setAnimation(true).setElementId('center').run();</script>
              <div class="centerblock">
                <h1><xsl:value-of select="//REPORT/TRANSLATE/ACCESSDENY"/></h1>
                <xsl:choose>
                  <xsl:when test="//REPORT/CODE='EXPIRE'">
                    <h4>
                      <xsl:call-template name="string-replace-all">
                        <xsl:with-param name="text" select="//REPORT/TRANSLATE/DATAEXPIRE" />
                        <xsl:with-param name="replace" select="'{date}'" />
                        <xsl:with-param name="by" select="//REPORT/PARAMS/PARAM" />
                      </xsl:call-template>
                    </h4>
                  </xsl:when>
                  <xsl:otherwise>
                    <h4><xsl:value-of select="//REPORT/TRANSLATE/CODEFAIL"/></h4>
                  </xsl:otherwise>
                </xsl:choose>
                <a class="button button-primary" target="_blank" href="http://my-stat.com/update/buy.php?driver={//REPORT/DRIVER}"><xsl:value-of select="//REPORT/TRANSLATE/BUYFULL"/></a>
                <span class="ortag"><xsl:value-of select="//REPORT/TRANSLATE/OR"/></span>
                <a class="button" onclick="jQuery('#uuidcontainer').show();return false;"><xsl:value-of select="//REPORT/TRANSLATE/ENTERCODE"/></a>
                <div id="uuidcontainer" style="display:none;">
                  <div class="domainselect" style="display:none;">
                    <label>
                      <xsl:value-of select="//REPORT/TRANSLATE/DELETEDOMAIN"/>
                      <select name="domain" id="uuiddomain"></select>
                    </label>
                  </div>
                  <label><xsl:value-of select="//REPORT/TRANSLATE/BUYCODE"/>:
                  <input type="text" name="code" id="uuidcode"/>
                  <script type="text/javascript"><![CDATA[
                    var text1 = "]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="//REPORT/TRANSLATE/FAILCODE"/></xsl:call-template><![CDATA[";
                  ]]></script>
                  <a id="btncheck" class="button button-small button-primary" onclick="var val=jQuery('#uuidcode').val();if(/^[0-9a-f]{{8}}-[0-9a-f]{{4}}-[1-5][0-9a-f]{{3}}-[89ab][0-9a-f]{{3}}-[0-9a-f]{{12}}$/i.test(val)==false){{alert(text1);return false;}}var el = this;jQuery(el).children('.spinner').show();loadAjax({{uuid:val,domain:jQuery('.domainselect').is(':visible')?jQuery('#uuiddomain').val():''}},function(data){{jQuery(el).children('.spinner').hide();getLicenseKey(data);}});return false;"><xsl:value-of select="//REPORT/TRANSLATE/CHECKBUTTON"/> <span class="spinner"></span></a>
                  </label><br/>
                  <a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-can-I-find-my-Purchase-Code-" target="_blank"><xsl:value-of select="//REPORT/TRANSLATE/CODEFIND"/></a>
                  <script type="text/javascript"><![CDATA[
                    jQuery(document).ready(function($){
                      $('#uuidcode').mask('hhhhhhhh-hhhh-vhhh-chhh-hhhhhhhhhhhh', {
                        'translation': {
                          h: {pattern: /[0-9A-f]/i},
                          v: {pattern: /[1-5]/},
                          c: {pattern: /[89ab]/i}
                        },
                        'placeholder': '________-____-____-____-____________'
                      });                      
                    });
                  ]]></script>
                </div>
              </div>
            </div>
          </div>
          <div id="postbox-container-1" class="postbox-container">
            <div id="side-sortables" class="meta-box-sortables ui-sortable" style="">
              <xsl:call-template name="menu"/>
            </div>
          </div>
        </div>    
      </div>
    </div>
    <script type="text/javascript"><![CDATA[
      function getLicenseKey(data){
        if(data.success){
          if(data.code=='OK' || data.code=='CHANGEDOMAIN'){
            var ddt = jQuery('#dataselectrange').data('range').split(' - ');
            jQuery('#btncheck').children('.spinner').show();
            loadDate(']]><xsl:value-of select="//REPORT/REPORT"/><![CDATA[',ddt[0],ddt[1]);
          }else if(data.code=='EXPIRE'){
            alert("]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="//REPORT/TRANSLATE/DATAEXPIRE"/></xsl:call-template><![CDATA[".replace(/\{date\}/,data.param[0]));
          }else if(data.code=='MAXDOMAIN'){
            var select = jQuery('#uuiddomain');
            select.html('');   
            jQuery.each(data.param, function(id, option){
              select.append(jQuery('<option></option>').val(option).html(option));   
            });
            jQuery('.domainselect').show();
            jQuery('#uuidcode').attr("readonly","true");
            jQuery('#btncheck').html("]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="//REPORT/TRANSLATE/DELETEDOMAIN"/></xsl:call-template><![CDATA["+' <span class="spinner"></span>');
            alert("]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="//REPORT/TRANSLATE/MAXDOMAIN"/></xsl:call-template><![CDATA[".replace(/\{max\}/,data.maxlicense));
          }
          return false;
        }
        alert(text1);
      }
      function loadDate(report,dateStart,dateEnd,param,callback){
        logoSVG.setAnimation(true);
        jQuery.ajax({
          url: ajaxurl,
          data: {
            action: 'mystat',
            report: report,
            datestart: dateStart,
            dateend: dateEnd,
            ajax: typeof param !='undefined'?true:false,
            param: param
          },
          dataType: typeof param !='undefined'?'json':'html',
          type: 'POST',
          success: function(data, textStatus){
            logoSVG.runtime = false;
            if(typeof callback =='function'){
              callback(data, textStatus);
              return true;
            }
            if(typeof viewChart !='undefined'){
              delete viewChart;
              viewChart = undefined;
            }
            jQuery('#mystat').html(data);
            jQuery(document).scrollTop(0);
            if(typeof viewChart !='undefined'){
              setTimeout(function(){
                viewChart();
              },100);
            }
          },
          error: function(p1,p2){
            logoSVG.runtime = false;
            document.location.reload();
          }
        });
      }
      function loadAjax(param,callback){
        var ddt = jQuery('#dataselectrange').data('range').split(' - ');
        loadDate(']]><xsl:value-of select="//REPORT/REPORT"/><![CDATA[',ddt[0],ddt[1],param,callback);
      }
      function selectMenu(el,report){
        var ddt = jQuery('#dataselectrange').data('range').split(' - ');
        if(!jQuery(el).parent().children('.spinner').is(':visible')){
          jQuery(el).width(jQuery(el).width()-40);
          jQuery(el).parent().children('.spinner').show();
          loadDate(report,ddt[0],ddt[1]);
        }
      }
      jQuery(document).ready(function($){
        $('.wrap .postbox-container .hndle, .wrap .postbox-container .handlediv').click(function(){
          $(this).parent('.postbox').toggleClass('closed');
        });
        $('#dataselectrange').dateRangePicker({
          showShortcuts: true,
          shortcuts: {
            'next-days': null,
            'next': null,
            'prev-days': [1,7,30],
            'prev' : ['week','month']
          },
          separator: ' - ',
          language: ']]><xsl:value-of select="translate(//REPORT/LANGUAGE,'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz')"/><![CDATA[',
          format: 'DD.MM.YYYY',
          endDate: new Date(]]><xsl:value-of select="//REPORT/TIME"/><![CDATA[ * 1000),
          showPrevMonth: true,
          startOfWeek: 'monday',
          minDays: 1,
          maxDays: 365,
          getValue: function(){
            return $('#dataselectrange .data').html();
          },
          setValue: function(s){
            $('#dataselectrange .data').html(s);
          }
        }).bind('datepicker-close',function(event,obj){
          if(obj.value!=$('#dataselectrange').attr('data-range')){
            $('#dataselectrange .spinner').show();
            loadDate(']]><xsl:value-of select="//REPORT/REPORT"/><![CDATA[',moment(obj.date1).format('DD.MM.YYYY'),moment(obj.date2).format('DD.MM.YYYY'));
            $('#dataselectrange').data('dateRangePicker').destroy();
          }
        });
      });
    ]]></script>
  </xsl:template>
  <xsl:template name="menu">
    <xsl:for-each select="//REPORT/MENU">
      <div class="postbox">
        <div class="handlediv"><br/></div>
        <h3 class="hndle"><span><xsl:value-of select="TITLE"/></span></h3>
        <div class="inside">
          <ul>
            <xsl:for-each select="ITEM">
            <li>
              <div>
              <span class="spinner"></span>
              <a class="button button-small">
                <xsl:if test="not(@disabled = 1)">
                  <xsl:attribute name="onclick">selectMenu(this,'<xsl:value-of select="@code"/>');return false;</xsl:attribute>
                </xsl:if>
                <xsl:if test="@disabled = 1">
                  <xsl:attribute name="class">button button-small disabled</xsl:attribute>
                </xsl:if>
                <xsl:if test="//REPORT/REPORT = @code">
                  <xsl:attribute name="class">button button-small button-primary</xsl:attribute>
                </xsl:if>
                <xsl:value-of select="."/>
              </a>
              </div>
            </li>
            </xsl:for-each>
          </ul>
        </div>
      </div>
    </xsl:for-each>
  </xsl:template>
</xsl:stylesheet>