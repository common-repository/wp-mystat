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
          <th class="manage-column" style="text-align:center;"><xsl:value-of select="//REPORT/TRANSLATE/NAME_ROBOT"/></th>
          <th class="manage-column" style="text-align:center;width:150px;"><xsl:value-of select="//REPORT/TRANSLATE/UNIQ"/></th>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <td colspan="2" class="manage-column"><xsl:value-of select="//REPORT/TRANSLATE/COUNT_ROBOT"/></td>
          <td class="manage-column" style="text-align:center;"><b><xsl:value-of select="count(//REPORT/INDICATORS/INDICATOR)"/></b></td>
        </tr>
        <tr>
          <td colspan="2"><xsl:value-of select="//REPORT/TRANSLATE/NOROBOTDETECT"/></td>
          <td class="center"><b><xsl:value-of select="//REPORT/INDICATORS/NOTSET"/></b></td>
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
                <td class="center"><xsl:value-of select="position()"/>.</td>
                <td>
                  <xsl:value-of select="ROBOT"/>
                </td>
                <td class="center">
                  <xsl:choose>
                    <xsl:when test="count(VERSION) &gt; 0">
                      <a onclick="jQuery('#extend{position()}').toggle();return false;" href="" class="btn btn-small"><xsl:value-of select="COUNT"/> »</a>
                    </xsl:when>
                    <xsl:otherwise>
                      <xsl:value-of select="COUNT"/>
                    </xsl:otherwise>
                  </xsl:choose>
                </td>
              </tr>
              <tr></tr>
              <tr id="extend{position()}" style="display:none;">
                <td colspan="3">
                  <table style="margin-left:150px;width:inherit;" class="table table-striped table-bordered">
                    <tr>
                      <th style="text-align:center;width:30px;">#</th>
                      <th style="text-align:center;min-width:200px;"><xsl:value-of select="//REPORT/TRANSLATE/VERSION"/></th>
                      <th style="text-align:center;width:40px;"><xsl:value-of select="//REPORT/TRANSLATE/UNIQ"/></th>
                    </tr>
                    <xsl:for-each select="VERSION">
                      <tr>
                        <td style="text-align:center;"><xsl:value-of select="position()"/></td>
                        <td><xsl:value-of select="@number"/></td>
                        <td style="text-align:center;"><xsl:value-of select="."/></td>
                      </tr>
                    </xsl:for-each>
                  </table>
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