<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:import href="joomla.xsl" />
  <xsl:output method="html"/>
  <xsl:template name="content">
    <style type="text/css">
      .wrap .mcolumn .welltitle{font-size:16px;line-height:16px;}
      .wrap .mcolumn .bigtext{font-size:80px;line-height:80px;text-align:center;font-weight:bold;margin-bottom:15px;}
      .wrap .mcolumn .middletext{font-size:40px;line-height:40px;text-align:center;font-weight:bold;}
      .wrap .graphic_small{margin-top:10px;margin-bottom:15px;}
      .wrap #j-main-container .container-fluid{padding-left:0;padding-right:0;}
    </style>
    <div class="container1-fluid">
      <div class="row1-fluid">
        <div class="span6 mcolumn">
          <div class="well well-large">
            <div class="welltitle"><xsl:value-of select="//REPORT/TRANSLATE/UNIQTODAY"/>:</div>
            <div class="bigtext"><xsl:value-of select="//REPORT/VISITTODAY"/></div>
          </div>
          <div class="well well-large">
            <div class="welltitle"><xsl:value-of select="//REPORT/TRANSLATE/UNIQYESTERDAY"/>:</div>
            <div class="middletext"><xsl:value-of select="//REPORT/VISITYESTERDAY"/></div>
          </div>
          <div class="well well-large">
            <div class="welltitle"><xsl:value-of select="//REPORT/TRANSLATE/ROBOTTODAY"/>:</div>
            <div class="middletext"><xsl:value-of select="//REPORT/ROBOTTODAY"/></div>
          </div>
          <div class="well well-large">
            <div class="welltitle"><xsl:value-of select="//REPORT/TRANSLATE/GEOGRAPHI"/>:</div>
            <div class="graphic_small" id="mystat_graphic_1"></div>
            <div style="text-align:right;">
              <a class="btn btn-small" onclick="selectDetail(this,'geoCountry');return false;"><xsl:value-of select="//REPORT/TRANSLATE/DETAIL"/> <span class="spinner"></span></a>
            </div>
          </div>
          <div class="well well-large">
            <div class="welltitle"><xsl:value-of select="//REPORT/TRANSLATE/PLATFORM"/>:</div>
            <div class="graphic_small" id="mystat_graphic_2"></div>
            <div style="text-align:right;">
              <a class="btn btn-small" onclick="selectDetail(this,'operatingSystem');return false;"><xsl:value-of select="//REPORT/TRANSLATE/DETAIL"/> <span class="spinner"></span></a>
            </div>
          </div>
          <div class="well well-large">
            <div class="welltitle"><xsl:value-of select="//REPORT/TRANSLATE/ROBOT"/>:</div>
            <div class="graphic_small" id="mystat_graphic_3"></div>
            <div style="text-align:right;">
              <a class="btn btn-small" onclick="selectDetail(this,'robots');return false;"><xsl:value-of select="//REPORT/TRANSLATE/DETAIL"/> <span class="spinner"></span></a>
            </div>
          </div>
        </div>
        <div class="span6 mcolumn">
          <div class="well well-large">
            <div class="welltitle"><xsl:value-of select="//REPORT/TRANSLATE/ONLINE"/>:</div>
            <div class="bigtext"><xsl:value-of select="//REPORT/ONLINE"/></div>
          </div>
          <div class="well well-large">
            <div class="welltitle"><xsl:value-of select="//REPORT/TRANSLATE/ROBOTONLINE"/>:</div>
            <div class="middletext"><xsl:value-of select="//REPORT/ONLINEROBOT"/></div>
          </div>
          <div class="well well-large">
            <div class="welltitle"><xsl:value-of select="//REPORT/TRANSLATE/ROBOTYESTERDAY"/>:</div>
            <div class="middletext"><xsl:value-of select="//REPORT/ROBOTYESTERDAY"/></div>
          </div>
          <div class="well well-large">
            <div class="welltitle"><xsl:value-of select="//REPORT/TRANSLATE/USER"/> - <i><xsl:value-of select="//REPORT/TRANSLATE/UNIQ"/></i>:</div>
            <div class="graphic_small" id="mystat_graphic_4"></div>
            <div style="text-align:right;">
              <a class="btn btn-small" onclick="selectDetail(this,'siteUsage');return false;"><xsl:value-of select="//REPORT/TRANSLATE/DETAIL"/> <span class="spinner"></span></a>
            </div>
          </div>
          <div class="well well-large">
            <div class="welltitle"><xsl:value-of select="//REPORT/TRANSLATE/USER"/> - <i><xsl:value-of select="//REPORT/TRANSLATE/VIEW"/></i>:</div>
            <div class="graphic_small" id="mystat_graphic_5"></div>
            <div style="text-align:right;">
              <a class="btn btn-small" onclick="selectDetail(this,'pageViewPerUser');return false;"><xsl:value-of select="//REPORT/TRANSLATE/DETAIL"/> <span class="spinner"></span></a>
            </div>
          </div>
          <div class="well well-large">
            <div class="welltitle"><xsl:value-of select="//REPORT/TRANSLATE/REFERER"/>:</div>
            <div class="graphic_small" id="mystat_graphic_6"></div>
            <div style="text-align:right;">
              <a class="btn btn-small" onclick="selectDetail(this,'referrer');return false;"><xsl:value-of select="//REPORT/TRANSLATE/DETAIL"/> <span class="spinner"></span></a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script type="text/javascript"><![CDATA[
      if(typeof google != 'undefined' && typeof google.visualization == 'undefined'){
        google.charts.load('current',{'mapsApiKey':'AIzaSyAkz6JyMuz7MB3cicc9GbQIZwK4GgAynwo','packages':['corechart','geochart'], 'language':']]><xsl:value-of select="//REPORT/LANGUAGE"/><![CDATA['});
        google.charts.setOnLoadCallback(viewChart);
      }
      function viewChart(){
        if(typeof google == 'undefined' || typeof google.visualization == 'undefined' || typeof google.visualization.DataTable == 'undefined'){return;}
        var options = {
          height: 200,
          legend: {
            position: 'labeled'
          },
          backgroundColor: '#f5f5f5',
          vAxis: {
            format: '#'
          },
          pieHole: 0.4,
          dataOpacity: 0.9,
          theme: 'maximized',
          focusTarget: 'category'
        };

        var data1 = new google.visualization.DataTable();
        data1.addColumn('string', '');
        data1.addColumn('string', '');
        data1.addColumn('number', "]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="//REPORT/TRANSLATE/USER"/></xsl:call-template><![CDATA[");
        data1.addRows(]]><xsl:call-template name="graphicjsondata_1"/><![CDATA[);
        var chart1 = new google.visualization.GeoChart(document.getElementById('mystat_graphic_1'));
        chart1.draw(data1, options);

        var data2 = new google.visualization.DataTable();
        data2.addColumn('string', '');
        data2.addColumn('number', "]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="//REPORT/TRANSLATE/USER"/></xsl:call-template><![CDATA[");
        data2.addRow(["]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="//REPORT/TRANSLATE/PLATFORM_MOBILE"/></xsl:call-template><![CDATA[",]]><xsl:value-of select="//REPORT/PLATFORMS/MOBILE"/><![CDATA[]);
        data2.addRow(["]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="//REPORT/TRANSLATE/PLATFORM_TABLET"/></xsl:call-template><![CDATA[",]]><xsl:value-of select="//REPORT/PLATFORMS/TABLET"/><![CDATA[]);
        data2.addRow(["]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="//REPORT/TRANSLATE/PLATFORM_DESKTOP"/></xsl:call-template><![CDATA[",]]><xsl:value-of select="//REPORT/PLATFORMS/DESKTOP"/><![CDATA[]);
        var chart2 = new google.visualization.PieChart(document.getElementById('mystat_graphic_2'));
        chart2.draw(data2, options);

        var data3 = new google.visualization.DataTable();
        data3.addColumn('datetime', '');
        data3.addColumn('number', "]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="concat(//REPORT/TRANSLATE/ROBOT,': ',//REPORT/TRANSLATE/UNIQ)"/></xsl:call-template><![CDATA[");
        data3.addRows(]]><xsl:call-template name="graphicjsondata_3"/><![CDATA[);
        var chart3 = new google.visualization.ColumnChart(document.getElementById('mystat_graphic_3'));
        chart3.draw(data3, options);

        var data4 = new google.visualization.DataTable();
        data4.addColumn('datetime', '');
        data4.addColumn('number', "]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="concat(//REPORT/TRANSLATE/USER,': ',//REPORT/TRANSLATE/UNIQ)"/></xsl:call-template><![CDATA[");
        data4.addRows(]]><xsl:call-template name="graphicjsondata_4"/><![CDATA[);
        var chart4 = new google.visualization.ColumnChart(document.getElementById('mystat_graphic_4'));
        chart4.draw(data4, options);

        var data5 = new google.visualization.DataTable();
        data5.addColumn('datetime', '');
        data5.addColumn('number', "]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="concat(//REPORT/TRANSLATE/USER,': ',//REPORT/TRANSLATE/VIEW)"/></xsl:call-template><![CDATA[");
        data5.addRows(]]><xsl:call-template name="graphicjsondata_5"/><![CDATA[);
        var chart5 = new google.visualization.ColumnChart(document.getElementById('mystat_graphic_5'));
        chart5.draw(data5, options);

        var data6 = new google.visualization.DataTable();
        data6.addColumn('string', '');
        data6.addColumn('number', "]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="//REPORT/TRANSLATE/USER"/></xsl:call-template><![CDATA[");
        data6.addRow(["]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="//REPORT/TRANSLATE/REFERER_SEARCH"/></xsl:call-template><![CDATA[",]]><xsl:value-of select="//REPORT/REFERER/SEARCH"/><![CDATA[]);
        data6.addRow(["]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="//REPORT/TRANSLATE/REFERER_SOCIAL"/></xsl:call-template><![CDATA[",]]><xsl:value-of select="//REPORT/REFERER/SOCIAL"/><![CDATA[]);
        data6.addRow(["]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="//REPORT/TRANSLATE/REFERER_EMAIL"/></xsl:call-template><![CDATA[",]]><xsl:value-of select="//REPORT/REFERER/EMAIL"/><![CDATA[]);
        data6.addRow(["]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="//REPORT/TRANSLATE/REFERER_OTHER"/></xsl:call-template><![CDATA[",]]><xsl:value-of select="//REPORT/REFERER/OTHER"/><![CDATA[]);
        data6.addRow(["]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="//REPORT/TRANSLATE/REFERER_DIRECT"/></xsl:call-template><![CDATA[",]]><xsl:value-of select="//REPORT/REFERER/DIRECT"/><![CDATA[]);
        var chart6 = new google.visualization.PieChart(document.getElementById('mystat_graphic_6'));
        chart6.draw(data6, options);
      }
      function selectDetail(el,report){
        var ddt = jQuery('#dataselectrange').data('range').split(' - ');
        loadDate(report,ddt[0],ddt[1]);
      }
    ]]></script>
  </xsl:template>
  <xsl:template name="graphicjsondata_1">
    [
      <xsl:if test="count(//REPORT/COUNTRIS/COUNTRY) > 0">
        <xsl:for-each select="//REPORT/COUNTRIS/COUNTRY">
          ["<xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="."/></xsl:call-template>","<xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="@name"/></xsl:call-template>",<xsl:value-of select="@count"/>]<xsl:if test="position() != last()">,</xsl:if>
        </xsl:for-each>
      </xsl:if>
    ]
  </xsl:template>
  <xsl:template name="graphicjsondata_3">
    [
      <xsl:if test="count(//REPORT/INDICATORS/INDICATOR) > 0">
        <xsl:for-each select="//REPORT/INDICATORS/INDICATOR">
          [new Date(<xsl:value-of select="TIMESTAMP"/> * 1000),<xsl:value-of select="ROBOT/UNIQ"/>]<xsl:if test="position() != last()">,</xsl:if>
        </xsl:for-each>
      </xsl:if>
    ]
  </xsl:template>
  <xsl:template name="graphicjsondata_4">
    [
      <xsl:if test="count(//REPORT/INDICATORS/INDICATOR) > 0">
        <xsl:for-each select="//REPORT/INDICATORS/INDICATOR">
          [new Date(<xsl:value-of select="TIMESTAMP"/> * 1000),<xsl:value-of select="USER/UNIQ"/>]<xsl:if test="position() != last()">,</xsl:if>
        </xsl:for-each>
      </xsl:if>
    ]
  </xsl:template>
  <xsl:template name="graphicjsondata_5">
    [
      <xsl:if test="count(//REPORT/INDICATORS/INDICATOR) > 0">
        <xsl:for-each select="//REPORT/INDICATORS/INDICATOR">
          [new Date(<xsl:value-of select="TIMESTAMP"/> * 1000),<xsl:value-of select="USER/VIEW"/>]<xsl:if test="position() != last()">,</xsl:if>
        </xsl:for-each>
      </xsl:if>
    ]
  </xsl:template>
</xsl:stylesheet>