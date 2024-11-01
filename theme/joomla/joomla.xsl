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
      .wrap #logo{position:absolute;left:10px;width:115px;height:115px;}
      .wrap .maintitle{margin-left:135px;}
      .wrap .subtitle{margin: 20px 0;margin-left:135px;}
      .wrap .head{background-color:#f5f5f5;position: absolute;left: 0;right: 0px;top: 122px;height: 110px;padding: 10px;border-bottom: 1px solid #e3e3e3;}
      .wrap .maincontent{margin-top:113px;}
      .wrap .menucontainer li a{cursor:pointer;}
      .wrap .period .text{margin-right:15px;}
      .wrap .icon{font-family:mystat;text-shadow: 1px 1px 3px rgba(150, 150, 150, 0.81);}
      .wrap .bottombtn{padding:1px 0;}
      .wrap .bottombtn .button32{white-space:normal;height: 45px;padding: 6px;margin:0 2px;}
      .wrap .icon.button32{font-size:32px;}
      .wrap #j-sidebar-container{margin-top: 8px;margin-bottom: 35px;}
      .wrap #j-main-container{margin-top: 30px;}
      .wrap .table .progress{margin-bottom:0px;height:10px;box-shadow:none;}
      .wrap .table tr:nth-child(even) .progress{background-color: #fff;background-image: linear-gradient(to bottom,#fff,#fff);}
      .wrap .table .progress .bar{font-size:10px;line-height:10px;}
      .wrap .table .center{text-align:center;}
      .wrap .screen{border:2px solid #aaa;background-color:#ccc;text-align:center;margin: 0 auto;}
      #system-message-container{position: relative;top: 122px;}
      #system-message-container .alert{border-radius: 0;margin: 0px -20px;}
      #system-message-container .alert .alert-message{margin:0;}
      #loading{
        background: rgba(255, 255, 255, .8) url('../media/jui/img/ajax-loader.gif') 50% 50% no-repeat;
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
    </style>
    <div class="wrap">
      <div class="head js-stools clearfix">
        <div id="logo" onclick="logoSVG.setAnimation(true);"></div>
        <script type="text/javascript">logoSVG.setSize(115).setAnimation(false).setElementId('logo').run();Joomla = {JText:{_:function(){}}};</script>
        <h2 class="maintitle"><xsl:value-of select="//REPORT/TITLE"/></h2>
        <p class="subtitle alert alert-info"><a><span><xsl:value-of select="//REPORT/SUBTITLE"/></span></a></p>
      </div>
      <div class="maincontent">
        <div class="">
          <div class="">
            <div id="j-sidebar-container" class="span2 menucontainer j-sidebar-container j-sidebar-visible">
              <div id="j-toggle-sidebar-wrapper">
              	<div id="j-toggle-button-wrapper" class="j-toggle-button-wrapper j-toggle-visible">
              		<div id="j-toggle-sidebar-button" class="j-toggle-sidebar-button hidden-phone hasTooltip" title="" type="button" onclick="toggleSidebar(false); return false;">
              	    <span id="j-toggle-sidebar-icon" class="icon-arrow-left-2"></span>
                  </div>
              	</div>
              	<div id="sidebar" class="sidebar">
              		<div class="sidebar-nav">
        						<ul id="submenu" class="nav nav-list">
                      <xsl:call-template name="menu"/>
      							</ul>
									</div>
	              </div>
	              <div id="j-toggle-sidebar"></div>
              </div>
            </div>
          </div>
          <div id="j-main-container" class="span10 j-toggle-main j-toggle-transition">
            <div class="span12 well well-small">
              <xsl:if test="//REPORT/BUTTON_HIDE/PERIOD">
                <xsl:attribute name="style">display:none;</xsl:attribute>
              </xsl:if>
              <div class="period"><span class="text"><xsl:value-of select="//REPORT/TRANSLATE/PERIODREPORT"/></span> <a class="btn btn-small" data-range="{//REPORT/PERIOD/START} - {//REPORT/PERIOD/END}" id="dataselectrange"><span class="data"><xsl:value-of select="//REPORT/PERIOD/START"/> - <xsl:value-of select="//REPORT/PERIOD/END"/></span></a></div>
              <div class="topbtn"></div>
            </div>
            <div class="row-fluid">
              <xsl:call-template name="content"/>
            </div>
            <xsl:if test="not(//REPORT/BUTTON_HIDE/EXPORT)">
              <div class="well well-small bottom">
                <div class="bottombtn">
                  <button class="btn button32 icon" title="{//REPORT/TRANSLATE/EXPORTXML}" onclick="document.location='{//REPORT/PATHEXPORT}&amp;report=export&amp;type=xml&amp;in={//REPORT/REPORT}';">&#40;</button>
                </div>
              </div>
            </xsl:if>
          </div>
        </div>    
      </div>
    </div>
    <script type="text/javascript"><![CDATA[
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
      function loadPage(page){
        var ddt = jQuery('#dataselectrange').data('range').split(' - ');
        loadDate(']]><xsl:value-of select="//REPORT/REPORT"/><![CDATA[',ddt[0],ddt[1],{page:page});
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
        if(typeof viewChart !='undefined'){
          viewChart();
        }
        $(window).on('load',function(){
          if(typeof viewChart !='undefined'){
            viewChart();
          }
        });
        $(window).resize(function(){
          if(typeof viewChart !='undefined'){
            viewChart();
          }
        });
        if($('.subhead .btn-toolbar').html().trim()==''){
          $('.subhead-collapse.collapse').hide();
          $('.head ').css('top','68px');
        }
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
            loadDate(']]><xsl:value-of select="//REPORT/REPORT"/><![CDATA[',moment(obj.date1).format('DD.MM.YYYY'),moment(obj.date2).format('DD.MM.YYYY'));
            $('#dataselectrange').data('dateRangePicker').destroy();
          }
        });
      });
    ]]></script>
    <div id="loading"></div>
  </xsl:template>
  <xsl:template name="content"></xsl:template>
  <xsl:template name="menu">
    <xsl:for-each select="//REPORT/MENU">
      <li class="nav-header"><xsl:value-of select="TITLE"/></li>
      <xsl:for-each select="ITEM">
      <li>
        <xsl:if test="//REPORT/REPORT = @code">
          <xsl:attribute name="class">active</xsl:attribute>
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
    </xsl:for-each>
  </xsl:template>
  <xsl:template name="pagination">
  	<xsl:param name="recordsPerPage"/>
  	<xsl:param name="records"/>
    <xsl:param name="currentPage" select="1"/>
    <xsl:param name="showAlwaysFirstAndLast" select="true"/>
    <xsl:variable name="numberOfRecords" select="count($records)"/>
    <xsl:variable name="lastPage" select="ceiling($numberOfRecords div $recordsPerPage)"/>
    <xsl:variable name="extremePagesLimit" select="3"/>
    <xsl:variable name="nearbyPagesLimit" select="2"/>
    <xsl:if test="$lastPage &gt; 1">
      <div class="pagination">
        <ul>
          <xsl:choose>
            <xsl:when test="$currentPage &gt; 1">
              <li><a class="first-page" href="" onclick="loadPage(1);return false;"><span class="icon-first"></span></a></li>
              <li><a class="prev-page" href="" onclick="loadPage({$currentPage - 1});return false;"><span class="icon-previous"></span></a></li>
              <xsl:for-each select="$records">
                <xsl:if test="position() &lt;= $extremePagesLimit">
                  <xsl:if test="position() &lt; $currentPage - $nearbyPagesLimit">
                    <li><a class="button-page" href="" onclick="loadPage({position()});return false;"><xsl:value-of select="position()"/></a></li>
                  </xsl:if>
                </xsl:if>
              </xsl:for-each>
              <xsl:if test="$extremePagesLimit + 1 &lt; $currentPage - $nearbyPagesLimit">
                <span class="sep-dots">...</span>
              </xsl:if>
              <xsl:for-each select="$records">
                <xsl:if test="position() &gt;= $currentPage - $nearbyPagesLimit">
                  <xsl:if test="position() &lt;= $currentPage - 1">
                    <li><a class="button-page" href="" onclick="loadPage({position()});return false;"><xsl:value-of select="position()"/></a></li>
                  </xsl:if>
                </xsl:if>
              </xsl:for-each>
            </xsl:when>
            <xsl:otherwise>
              <xsl:if test="$showAlwaysFirstAndLast = 'true'">
                <li class="disabled"><a class="first-page"><span class="icon-first"></span></a></li>
                <li class="disabled"><a class="prev-page"><span class="icon-previous"></span></a></li>
              </xsl:if>
            </xsl:otherwise>
          </xsl:choose>
          <li class="active"><a class="active-page" onclick="loadPage({$currentPage});return false;"><xsl:value-of select="$currentPage"/></a></li>
          <xsl:choose>
            <xsl:when test="$currentPage &lt; $lastPage">
              <xsl:for-each select="$records">
                <xsl:if test="position() &gt;= $currentPage + 1">
                  <xsl:if test="position() &lt;= $currentPage + $nearbyPagesLimit">
                    <xsl:if test="position() &lt;= $lastPage">
                      <li><a class="button-page" href="" onclick="loadPage({position()});return false;"><xsl:value-of select="position()"/></a></li>
                    </xsl:if>
                  </xsl:if>
                </xsl:if>
              </xsl:for-each>
              <xsl:if test="($lastPage - $extremePagesLimit) &gt; ($currentPage + $nearbyPagesLimit)">
                <span class="sep-dots">...</span>
              </xsl:if>
              <xsl:for-each select="$records">
                <xsl:if test="position() &gt;= $lastPage - $extremePagesLimit + 1">
                  <xsl:if test="position() &lt;= $lastPage">
                    <xsl:if test="position() &gt; $currentPage + $nearbyPagesLimit">
                      <li><a class="button-page" href="" onclick="loadPage({position()});return false;"><xsl:value-of select="position()"/></a></li>
                    </xsl:if>
                  </xsl:if>
                </xsl:if>
              </xsl:for-each>
              <li><a class="next-page" href="" onclick="loadPage({$currentPage + 1});return false;"><span class="icon-next"></span></a></li>
              <li><a class="last-page" href="" onclick="loadPage({$lastPage});return false;"><span class="icon-last"></span></a></li>
            </xsl:when>
            <xsl:otherwise>
              <xsl:if test="$showAlwaysFirstAndLast = 'true'">
                <li class="disabled"><a class="next-page"><span class="icon-next"></span></a></li>
                <li class="disabled"><a class="last-page"><span class="icon-last"></span></a></li>
              </xsl:if>
            </xsl:otherwise>
          </xsl:choose>
        </ul>
      </div>          
    </xsl:if>
  </xsl:template>
</xsl:stylesheet>