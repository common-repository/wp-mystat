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
          <th class="manage-column" style="text-align:center;"><xsl:value-of select="//REPORT/TRANSLATE/URI"/></th>
          <th class="manage-column" style="text-align:center;width:100px;"><xsl:value-of select="//REPORT/TRANSLATE/SITELINK"/></th>
          <th class="manage-column" style="text-align:center;width:100px;"><xsl:value-of select="//REPORT/TRANSLATE/VIEW"/></th>
          <th class="manage-column" style="text-align:center;width:150px;"><xsl:value-of select="//REPORT/TRANSLATE/USER"/></th>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <td colspan="4" class="manage-column"><xsl:value-of select="//REPORT/TRANSLATE/DIRECTVISIT"/></td>
          <td class="manage-column" style="text-align:center;"><b><xsl:value-of select="//REPORT/INDICATORS/DIRECT_VISIT"/></b></td>
        </tr>
        <tr>
          <td colspan="4" class="manage-column"><xsl:value-of select="//REPORT/TRANSLATE/REFERRERVISIT"/></td>
          <td class="manage-column" style="text-align:center;"><b><xsl:value-of select="count(//REPORT/INDICATORS/INDICATOR)"/></b></td>
        </tr>
      </tfoot>
      <tbody>
        <xsl:variable name="maxUniq">
          <xsl:call-template name="maximum">
            <xsl:with-param name="pSequence" select="//REPORT/INDICATORS/INDICATOR/COUNT"/>
          </xsl:call-template>
        </xsl:variable>
        <xsl:variable name="minUniq">
          <xsl:call-template name="minimum">
            <xsl:with-param name="pSequence" select="//REPORT/INDICATORS/INDICATOR/COUNT"/>
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
                <td>
                  <a href="{REFERRER}" target="_blank"><xsl:value-of select="REFERRER"/></a>
                </td>
                <td align="center">
                  <a onclick="jQuery('#extend{position()}').toggle();return false;" href="" class="button button-small"><xsl:value-of select="count(URI)"/> »</a>
                </td>
                <td align="center">
                  <xsl:value-of select="COUNT"/>
                </td>
                <td align="center">
                  <xsl:value-of select="USER"/>
                </td>
              </tr>
              <tr id="extend{position()}" style="display:none;">
                <xsl:if test="position() mod 2 = 1">
                  <xsl:attribute name="class">alternate</xsl:attribute>
                </xsl:if>
                <td colspan="5">
                  <xsl:for-each select="URI">
                    <div style="margin-left:100px;border-bottom: 1px solid #CCC;"><xsl:value-of select="position()"/>. <a href="{.}" target="_blank">
                    <xsl:choose>
                      <xsl:when test=".='/'">
                        <xsl:value-of select="//REPORT/TRANSLATE/MAINPAGE"/>
                      </xsl:when>
                      <xsl:otherwise>
                        <xsl:value-of select="."/>
                      </xsl:otherwise>
                    </xsl:choose>
                    </a></div>
                  </xsl:for-each>
                </td>
              </tr>
              <tr>
                <xsl:if test="position() mod 2 = 1">
                  <xsl:attribute name="class">alternate</xsl:attribute>
                </xsl:if>
                <xsl:if test="$maxUniq&gt;0">
                  <td colspan="5" style="padding: 0 8px 3px 8px;">
                    <div class="progress"><div class="percent"><xsl:value-of select='format-number(COUNT * 100 div sum(//REPORT/INDICATORS/INDICATOR/COUNT),"#.##")'/>%</div><div class="bar">
                      <xsl:attribute name="style">width:<xsl:value-of select='COUNT * 100 div sum(//REPORT/INDICATORS/INDICATOR/COUNT)'/>%</xsl:attribute>
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

  <xsl:template name="graphic">
    <div id="mystat_graphic" class="postbox"></div>
    <script type="text/javascript"><![CDATA[
      if(typeof google != 'undefined' && typeof google.visualization == 'undefined'){
        google.charts.load('current',{'mapsApiKey':'AIzaSyAkz6JyMuz7MB3cicc9GbQIZwK4GgAynwo','packages':['corechart'], 'language':']]><xsl:value-of select="//REPORT/LANGUAGE"/><![CDATA['});
        google.charts.setOnLoadCallback(viewChart);
      }
      function viewChart(){
        if(typeof google == 'undefined' || typeof google.visualization == 'undefined' || typeof google.visualization.DataTable == 'undefined'){return;}
        var data = new google.visualization.DataTable();
        data.addColumn('string', '');
        data.addColumn('number', '');
        data.addRow(["]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="//REPORT/TRANSLATE/DIRECTVISIT"/></xsl:call-template><![CDATA[",]]><xsl:value-of select="//REPORT/INDICATORS/DIRECT_VISIT"/><![CDATA[]);
        data.addRow(["]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="//REPORT/TRANSLATE/REFERRERVISIT"/></xsl:call-template><![CDATA[",]]><xsl:value-of select="count(//REPORT/INDICATORS/INDICATOR)"/><![CDATA[]);
        var options = {
          height: 400,
          vAxis: {
            format: '#'
          },
          dataOpacity: 0.9,
          focusTarget: 'category'
        };
        var chart = new google.visualization.PieChart(document.getElementById('mystat_graphic'));
        chart.draw(data, options);
      }
    ]]></script>
  </xsl:template>

</xsl:stylesheet>