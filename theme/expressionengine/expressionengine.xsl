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
      .box .wrap{padding:1px 0;}
      .box table{border:0;margin:0;}
      .box th,.box td{border-radius:0 !important;}
      .wrap #logo{position:absolute;left:10px;width:115px;height:115px;top:-17px;}
      .wrap .maintitle{margin-left:145px;border-width: 1px;font-size: 18px;}
      .wrap .subtitle{margin: 20px 0;margin-left:145px;}
      .wrap .head{background-color:#f5f5f5;position: absolute;left: 0;right: 0px;top: 122px;height: 110px;padding: 10px;border-bottom: 1px solid #e3e3e3;}
      .wrap .maincontent{margin-top:113px;}
      .wrap .maincontent .sidebar{margin-bottom: 10px;}
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
      .wrap #j-sidebar-container{margin-top: 8px;margin-bottom: 35px;}
      .wrap #j-main-container{margin-top: 30px;}
      .wrap .screen{border:2px solid #aaa;background-color:#ccc;text-align:center;margin: 0 auto;}
      .wrap .table .progress{margin-bottom:0px;height:10px;box-shadow:none;}
      .wrap .table tr:nth-child(even) .progress{background-color: #fff;background-image: linear-gradient(to bottom,#fff,#fff);}
      .wrap .table .progress .bar{font-size:10px;line-height:10px;padding:0;float: left;text-align: center;text-shadow: 0 -1px 0 rgba(0,0,0,0.25);background-color: #0e90d2;box-sizing: border-box;color:#fff;}
      .wrap .table .center{text-align:center;}
      .wrap .table{table-layout: fixed;}
      .wrap table td{text-overflow: ellipsis;overflow: hidden;white-space: nowrap;}
      .wrap table tfoot td{background-color:#eee;}
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
    </style>
    <div class="wrap">
      <div class="col w-16 last">
        <div id="logo" onclick="logoSVG.setAnimation(true);"></div>
        <h2 class="maintitle"><xsl:value-of select="//REPORT/TITLE"/></h2>
        <div class="subtitle"><xsl:value-of select="//REPORT/SUBTITLE"/></div>
      </div>
      <div class="maincontent col-group">
        <div class="">
          <div class="col w-4">
            <div class="box sidebar">
              <xsl:call-template name="menu"/>
            </div>
          </div>
          <div class="col w-12">
            <div class="breadcrumb" style="display:block;">
              <xsl:if test="//REPORT/BUTTON_HIDE/PERIOD">
                <xsl:attribute name="style">display:none;</xsl:attribute>
              </xsl:if>
              <div class="period"><span class="text"><xsl:value-of select="//REPORT/TRANSLATE/PERIODREPORT"/></span> <a class="btn btn-small" data-range="{//REPORT/PERIOD/START} - {//REPORT/PERIOD/END}" id="dataselectrange"><span class="data"><xsl:value-of select="//REPORT/PERIOD/START"/> - <xsl:value-of select="//REPORT/PERIOD/END"/></span></a></div>
              <div class="topbtn"></div>
            </div>
            <div class="col-group">
              <xsl:if test="//REPORT/REPORT != //REPORT/DEFAULTREPORT">
                <xsl:attribute name="class">box</xsl:attribute>
              </xsl:if>
              <xsl:call-template name="content"/>
            </div>
            <xsl:if test="not(//REPORT/BUTTON_HIDE/EXPORT)">
              <div class="breadcrumb" style="display:block;margin-top:10px;">
                <div class="bottombtn">
                  <button class="btn button32 icon" title="{//REPORT/TRANSLATE/EXPORTXML}" onclick="document.location='{//REPORT/PATHEXPORT}&amp;report=export&amp;type=xml&amp;in={//REPORT/REPORT}';return false;">&#40;</button>
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
          url: document.location,
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
        logoSVG.setSize(115).setAnimation(false).setElementId('logo').run();
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
  <xsl:template name="pagination">
  	<xsl:param name="recordsPerPage"/>
  	<xsl:param name="records"/>
    <xsl:param name="currentPage" select="1"/>
    <xsl:param name="showAlwaysFirstAndLast" select="false"/>
    <xsl:variable name="numberOfRecords" select="count($records)"/>
    <xsl:variable name="lastPage" select="ceiling($numberOfRecords div $recordsPerPage)"/>
    <xsl:variable name="extremePagesLimit" select="3"/>
    <xsl:variable name="nearbyPagesLimit" select="2"/>
    <xsl:if test="$lastPage &gt; 1">
      <div class="paginate" style="margin: 10px;">
        <ul>
          <xsl:choose>
            <xsl:when test="$currentPage &gt; 1">
              <li><a class="first-page" href="" onclick="loadPage(1);return false;">«</a></li>
              <li><a class="prev-page" href="" onclick="loadPage({$currentPage - 1});return false;">‹</a></li>
              <xsl:for-each select="$records">
                <xsl:if test="position() &lt;= $extremePagesLimit">
                  <xsl:if test="position() &lt; $currentPage - $nearbyPagesLimit">
                    <li><a class="button-page" href="" onclick="loadPage({position()});return false;"><xsl:value-of select="position()"/></a></li>
                  </xsl:if>
                </xsl:if>
              </xsl:for-each>
              <xsl:if test="$extremePagesLimit + 1 &lt; $currentPage - $nearbyPagesLimit">
                <li class="sep-dots"><a>...</a></li>
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
                <li class="disabled"><a class="first-page">«</a></li>
                <li class="disabled"><a class="prev-page">‹</a></li>
              </xsl:if>
            </xsl:otherwise>
          </xsl:choose>
          <li class="active"><a class="active-page act" onclick="loadPage({$currentPage});return false;"><xsl:value-of select="$currentPage"/></a></li>
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
                <li class="sep-dots"><a>...</a></li>
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
              <li><a class="next-page" href="" onclick="loadPage({$currentPage + 1});return false;">›</a></li>
              <li><a class="last-page" href="" onclick="loadPage({$lastPage});return false;">»</a></li>
            </xsl:when>
            <xsl:otherwise>
              <xsl:if test="$showAlwaysFirstAndLast = 'true'">
                <li class="disabled"><a class="next-page">›</a></li>
                <li class="disabled"><a class="last-page">»</a></li>
              </xsl:if>
            </xsl:otherwise>
          </xsl:choose>
        </ul>
      </div>          
    </xsl:if>
  </xsl:template>
</xsl:stylesheet>