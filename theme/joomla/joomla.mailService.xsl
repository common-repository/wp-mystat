<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:import href="joomla.xsl" />
  <xsl:output method="html"/>
  <xsl:template name="content">

    <xsl:call-template name="pagination">
  		<xsl:with-param name="currentPage" select="//REPORT/INDICATORS/CURRENT_PAGE"/>
  		<xsl:with-param name="recordsPerPage" select="//REPORT/INDICATORS/PER_PAGE" />
  		<xsl:with-param name="records" select="//REPORT/INDICATORS/INDICATOR"/>
  	</xsl:call-template>

    <table class="table table-striped table-bordered">
      <thead>
        <tr>
          <th style="text-align:center;width:40px;">#</th>
          <th class="manage-column" style="text-align:center;"><xsl:value-of select="//REPORT/TRANSLATE/NAME"/></th>
          <th class="manage-column" style="text-align:center;width:150px;"><xsl:value-of select="//REPORT/TRANSLATE/VISIT"/></th>
        </tr>
      </thead>
      <tfoot>
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
                <td class="center"><xsl:value-of select="position()"/>.</td>
                <td>
                  <xsl:value-of select="ENGINE"/>
                </td>
                <td class="center">
                  <xsl:value-of select="COUNT"/>
                </td>
              </tr>
              <tr></tr>
              <tr>
                <xsl:if test="$maxUniq&gt;0">
                  <td colspan="3" style="padding: 0px;">
                    <div class="progress">
                      <div class="bar">
                        <xsl:attribute name="style">width:<xsl:value-of select='COUNT * 100 div sum(//REPORT/INDICATORS/INDICATOR/COUNT)'/>%</xsl:attribute>
                        <xsl:value-of select='format-number(COUNT * 100 div sum(//REPORT/INDICATORS/INDICATOR/COUNT),"#.##")'/>%
                      </div>
                    </div>
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
</xsl:stylesheet>