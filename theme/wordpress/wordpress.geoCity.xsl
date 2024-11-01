<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:import href="wordpress.xsl" />
  <xsl:output method="html"/>
  <xsl:template name="content">

    <xsl:call-template name="graphic"/>

    <xsl:call-template name="pagination">
  		<xsl:with-param name="currentPage" select="//REPORT/INDICATORS/CURRENT_PAGE"/>
  		<xsl:with-param name="recordsPerPage" select="//REPORT/INDICATORS/PER_PAGE" />
  		<xsl:with-param name="records" select="//REPORT/INDICATORS/INDICATOR"/>
  	</xsl:call-template>

    <table class="widefat">
      <thead>
        <tr>
          <th style="text-align:center;width:40px;">#</th>
          <th style="text-align:center;width:40px;">&#160;</th>
          <th class="manage-column" style="text-align:center;"><xsl:value-of select="//REPORT/TRANSLATE/CITY"/></th>
          <th class="manage-column" style="text-align:center;width:150px;"><xsl:value-of select="//REPORT/TRANSLATE/UNIQ"/></th>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <td>&#160;</td>
          <td colspan="2"><xsl:value-of select="//REPORT/TRANSLATE/NOCITYDETECT"/></td>
          <td style="text-align:center;"><xsl:value-of select="//REPORT/INDICATORS/NOTSET"/></td>
        </tr>
      </tfoot>
      <tbody>
        <xsl:variable name="maxUniq">
          <xsl:call-template name="maximum">
            <xsl:with-param name="pSequence" select="//REPORT/INDICATORS/INDICATOR/CITY/@count"/>
          </xsl:call-template>
        </xsl:variable>
        <xsl:variable name="minUniq">
          <xsl:call-template name="minimum">
            <xsl:with-param name="pSequence" select="//REPORT/INDICATORS/INDICATOR/CITY/@count"/>
          </xsl:call-template>
        </xsl:variable>
        <xsl:for-each select="//REPORT/INDICATORS/INDICATOR">
          <xsl:if test="position() &gt; //REPORT/INDICATORS/PER_PAGE * (//REPORT/INDICATORS/CURRENT_PAGE - 1)">
            <xsl:if test="position() &lt;= //REPORT/INDICATORS/PER_PAGE * //REPORT/INDICATORS/CURRENT_PAGE">
              <tr>
                <xsl:if test="position() mod 2 = 1">
                  <xsl:attribute name="class">alternate</xsl:attribute>
                </xsl:if>
                <td align="center"><xsl:value-of select="position()"/>.</td>
                <td align="center" style="padding-top:5px;padding-bottom:0;">
                  <xsl:if test="CITY/@flag != ''">
                    <img src="{//REPORT/PATHTOCACHE}{CITY/@flag}"/>
                  </xsl:if>
                </td>
                <td>
                  <xsl:value-of select="CITY"/> (<xsl:value-of select="CITY/@name"/>)
                </td>
                <td align="center">
                  <xsl:value-of select="CITY/@count"/>
                </td>
              </tr>
              <tr>
                <xsl:if test="position() mod 2 = 1">
                  <xsl:attribute name="class">alternate</xsl:attribute>
                </xsl:if>
                <xsl:if test="$maxUniq&gt;0">
                  <td colspan="4" style="padding: 0 8px 3px 8px;">
                    <div class="progress"><div class="percent"><xsl:value-of select='format-number(CITY/@count * 100 div sum(//REPORT/INDICATORS/INDICATOR/CITY/@count),"#.##")'/>%</div><div class="bar">
                      <xsl:attribute name="style">width:<xsl:value-of select='CITY/@count * 100 div sum(//REPORT/INDICATORS/INDICATOR/CITY/@count)'/>%</xsl:attribute>
                    </div></div>
                  </td>
                </xsl:if>
              </tr>
            </xsl:if>
          </xsl:if>
        </xsl:for-each>
      </tbody>
    </table>

    <xsl:call-template name="pagination">
  		<xsl:with-param name="currentPage" select="//REPORT/INDICATORS/CURRENT_PAGE"/>
  		<xsl:with-param name="recordsPerPage" select="//REPORT/INDICATORS/PER_PAGE" />
  		<xsl:with-param name="records" select="//REPORT/INDICATORS/INDICATOR"/>
  	</xsl:call-template>

  </xsl:template>

  <xsl:template name="graphicjsondata">
    [
      <xsl:if test="count(//REPORT/INDICATORS/INDICATOR) > 0">
        <xsl:for-each select="//REPORT/INDICATORS/INDICATOR">
          ["<xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="CITY/@country"/></xsl:call-template>, <xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="CITY"/></xsl:call-template>","<xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="CITY"/></xsl:call-template> (<xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="CITY/@name"/></xsl:call-template>)",<xsl:value-of select="CITY/@count"/>]<xsl:if test="position() != last()">,</xsl:if>
        </xsl:for-each>
      </xsl:if>
    ]
  </xsl:template>
  <xsl:template name="graphic">
    <div id="mystat_graphic" class="postbox"></div>
    <script type="text/javascript"><![CDATA[
      if(typeof google != 'undefined' && typeof google.visualization == 'undefined'){
        google.charts.load('current',{'mapsApiKey':'AIzaSyAkz6JyMuz7MB3cicc9GbQIZwK4GgAynwo','packages':['corechart','geochart'], 'language':']]><xsl:value-of select="//REPORT/LANGUAGE"/><![CDATA['});
        google.charts.setOnLoadCallback(viewChart);
      }
      function viewChart(){
        if(typeof google == 'undefined' || typeof google.visualization == 'undefined' || typeof google.visualization.DataTable == 'undefined'){return;}
        var data = new google.visualization.DataTable();
        data.addColumn('string', '');
        data.addColumn('string', '');
        data.addColumn('number', "]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="//REPORT/TRANSLATE/UNIQ"/></xsl:call-template><![CDATA[");
        data.addRows(]]><xsl:call-template name="graphicjsondata"/><![CDATA[);
        var options = {
          height: 400,
          forceIFrame: true,
          legend: {
            position: 'labeled'
          },
          vAxis: {
            format: '#'
          },
          displayMode: 'markers',
          theme: 'maximized',
          dataOpacity: 0.9,
          focusTarget: 'category'
        };
        var chart = new google.visualization.GeoChart(document.getElementById('mystat_graphic'));
        chart.draw(data, options);
      }
    ]]></script>
  </xsl:template>

</xsl:stylesheet>