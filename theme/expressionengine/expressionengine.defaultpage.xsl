<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:import href="../template.xsl" />
  <xsl:output method="html"/>
  <xsl:template match="/">
    <style type="text/css">
      @font-face {
        font-family: 'mystat';
        src: url('<xsl:value-of select="//REPORT/PATHTOASSET"/>mystat.eot');
        src: url('<xsl:value-of select="//REPORT/PATHTOASSET"/>mystat.eot?#iefix') format('embedded-opentype'),
             url('<xsl:value-of select="//REPORT/PATHTOASSET"/>mystat.woff2') format('woff2'),
             url('<xsl:value-of select="//REPORT/PATHTOASSET"/>mystat.woff') format('woff'),
             url('<xsl:value-of select="//REPORT/PATHTOASSET"/>mystat.ttf') format('truetype'),
             url('<xsl:value-of select="//REPORT/PATHTOASSET"/>mystat.svg#mystatregular') format('svg');
        font-weight: normal;
        font-style: normal;
      }
      body{cursor:default;}
      .wrap {padding:0;}
      .wrap .maincontent{margin-top:0px;}
      .wrap .maincontent .sidebar h2{margin: 0;}
      .wrap .maincontent .sidebar ul{padding: 10px 0 10px 10px;margin:0;}
      .wrap .maincontent .sidebar ul li{line-height: initial;}
      .wrap .maincontent .sidebar ul li a{cursor:pointer;}
      .wrap .maincontent .col{padding: 0 10px;}
      .wrap .menucontainer li a{cursor:pointer;}
      .wrap .period .text{margin-right:15px;}
      .wrap .icon{font-family:mystat;text-shadow: 1px 1px 3px rgba(150, 150, 150, 0.81);}
      .wrap .bottombtn{padding:1px 0;}
      .wrap .bottombtn .button32{white-space:normal;height: 45px;padding: 6px;margin:0 2px;}
      .wrap .icon.button32{font-size:32px;}
      .wrap #j-sidebar-container{margin-top: 0px;margin-bottom: 35px;}
      .wrap #j-main-container{margin-top: 15px;}
      .wrap .centerblock{margin:20px auto;text-align:center;}
      .wrap .ortag{margin: 0px 10px;top: 4px;position: relative;}
      .wrap #uuidcontainer{margin-top:30px;}
      .wrap #appendedInputButtons{width:300px;}
      .wrap .domainselect{margin-bottom:10px;}
      .wrap #uuiddomain{margin-left:10px;}
      .wrap select{width:350px;}
      .wrap .btn-primary{position: relative;}
      .wrap .btn-primary .spinner{position:absolute;right: -35px;top:-2px;}
      #loading{
        background-color: rgba(255, 255, 255, .8);
        top: 0;
        left:0;
        width: 100%;
        height: 100%;
        position: fixed;
        opacity: 0.8;
        -ms-filter: progid:DXImageTransform.Microsoft.Alpha(Opacity = 80);
        filter: alpha(opacity = 80);
        display: none;
      }
      @media (min-width: 768px){
        .wrap .row-fluid [class*="span"]{margin-left: 2.13%;}
        .wrap .row-fluid [class*="span"]:first-child{margin-left: 0px;}
      }
      .date-picker-wrapper .drp_top-bar .apply-btn{background: #2EA2CC;border-color: #0074A2;-webkit-box-shadow: inset 0 1px 0 rgba(120, 200, 230, 0.5),0 1px 0 rgba(0, 0, 0, 0.15);box-shadow: inset 0 1px 0 rgba(120, 200, 230, 0.5),0 1px 0 rgba(0, 0, 0, 0.15);color: #FFF;text-decoration: none;}
    </style>
    <div class="wrap">
      <div class="maincontent col-group">
        <div class="">
          <div class="col w-4">
            <div class="box sidebar">
              <xsl:call-template name="menu"/>
            </div>
          </div>
          <div class="col w-12">
            <div class="breadcrumb" style="display:block;">
              <div class="period"><span class="text"><xsl:value-of select="//REPORT/TRANSLATE/PERIODREPORT"/></span> <a class="btn btn-small" data-range="{//REPORT/PERIOD/START} - {//REPORT/PERIOD/END}" id="dataselectrange"><span class="data"><xsl:value-of select="//REPORT/PERIOD/START"/> - <xsl:value-of select="//REPORT/PERIOD/END"/></span></a></div>
              <div class="topbtn"></div>
            </div>
            <div class="col-group">
              <div class="box" style="margin-top:10px;">
                <div id="center" class="centerblock"></div>
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
                  <a class="btn action" target="_blank" href="http://my-stat.com/update/buy.php?driver={//REPORT/DRIVER}"><xsl:value-of select="//REPORT/TRANSLATE/BUYFULL"/></a>
                  <span class="ortag"><xsl:value-of select="//REPORT/TRANSLATE/OR"/></span>
                  <a class="btn" onclick="jQuery('#uuidcontainer').show();return false;"><xsl:value-of select="//REPORT/TRANSLATE/ENTERCODE"/></a>
                  <div id="uuidcontainer" style="display:none;">
                    <div class="domainselect" style="display:none;">
                      <label>
                        <xsl:value-of select="//REPORT/TRANSLATE/DELETEDOMAIN"/>
                        <select name="domain" id="uuiddomain"></select>
                      </label>
                    </div>
                    <label><xsl:value-of select="//REPORT/TRANSLATE/BUYCODE"/>:
                    <div class="input-append">
                      <input type="text" id="appendedInputButtons" name="code"/>
                      <button type="button" id="btncheck" class="btn action" onclick="var val=jQuery('#appendedInputButtons').val();if(/^[0-9a-f]{{8}}-[0-9a-f]{{4}}-[1-5][0-9a-f]{{3}}-[89ab][0-9a-f]{{3}}-[0-9a-f]{{12}}$/i.test(val)==false){{alert(text1);return false;}}var el = this;jQuery(el).children('.spinner').show();loadAjax({{uuid:val,domain:jQuery('.domainselect').is(':visible')?jQuery('#uuiddomain').val():''}},function(data){{jQuery(el).children('.spinner').hide();getLicenseKey(data);}});return false;"><xsl:value-of select="//REPORT/TRANSLATE/CHECKBUTTON"/> <span class="spinner"></span></button>
                    </div>
                    <script type="text/javascript"><![CDATA[
                      var text1 = "]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="//REPORT/TRANSLATE/FAILCODE"/></xsl:call-template><![CDATA[";
                    ]]></script>
                    </label><br/>
                    <a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-can-I-find-my-Purchase-Code-" target="_blank"><xsl:value-of select="//REPORT/TRANSLATE/CODEFIND"/></a>
                    <script type="text/javascript"><![CDATA[
                      jQuery(document).ready(function($){
                        $('#appendedInputButtons').mask('hhhhhhhh-hhhh-vhhh-chhh-hhhhhhhhhhhh', {
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
            jQuery('#appendedInputButtons').attr("readonly","true");
            jQuery('#btncheck').html("]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="//REPORT/TRANSLATE/DELETEDOMAIN"/></xsl:call-template><![CDATA["+' <span class="spinner"></span>');
            alert("]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="//REPORT/TRANSLATE/MAXDOMAIN"/></xsl:call-template><![CDATA[".replace(/\{max\}/,data.maxlicense));
          }
          return false;
        }
        alert(text1);
      }
      function loadDate(report,dateStart,dateEnd,param,callback){
        jQuery('#loading').show();
        logoSVG.setAnimation(true);
        jQuery.ajax({
          url: document.location+'&format=raw',
          data: {
            report: report,
            datestart: dateStart,
            dateend: dateEnd,
            ajax: typeof callback =='function'?true:false,
            param: param
          },
          dataType: typeof callback =='function'?'json':'html',
          type: 'POST',
          success: function(data, textStatus){
            jQuery('#loading').hide();
            logoSVG.setAnimation(false);
            if(typeof callback =='function'){
              callback(data, textStatus);
              return true;
            }
            logoSVG.runtime = false;
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
          error: function(){
            jQuery('#loading').hide();
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
        loadDate(report,ddt[0],ddt[1]);
      }
      jQuery(document).ready(function($){
        logoSVG.setSize(256).setAnimation(true).setElementId('center').run();
        if(typeof viewChart !='undefined'){
          viewChart();
        }
        $(window).load(function(){
          if(typeof viewChart !='undefined'){
            viewChart();
          }
        });
        $(window).resize(function(){
          if(typeof viewChart !='undefined'){
            viewChart();
          }
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
    <div id="loading"></div>
  </xsl:template>
  <xsl:template name="menu">
    <xsl:for-each select="//REPORT/MENU">
      <h2><xsl:value-of select="TITLE"/></h2>
      <ul>
      <xsl:for-each select="ITEM">
      <li>
        <xsl:if test="//REPORT/REPORT = @code">
          <xsl:attribute name="class">act</xsl:attribute>
        </xsl:if>
        <xsl:if test="@disabled = 1">
          <xsl:attribute name="class">disabled</xsl:attribute>
        </xsl:if>
        <a>
          <xsl:if test="not(@disabled = 1)">
            <xsl:attribute name="onclick">selectMenu(this,'<xsl:value-of select="@code"/>');return false;</xsl:attribute>
          </xsl:if>
          <xsl:value-of select="."/>
        </a>
      </li>
      </xsl:for-each>
      </ul>
    </xsl:for-each>
  </xsl:template>
</xsl:stylesheet>