<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:import href="expressionengine.xsl" />
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
          <th style="text-align:center;width:100px;">&#160;</th>
          <th class="manage-column" style="text-align:center;"><xsl:value-of select="//REPORT/TRANSLATE/RESOLUTION"/></th>
          <th class="manage-column" style="text-align:center;width:150px;"><xsl:value-of select="//REPORT/TRANSLATE/UNIQ"/></th>
        </tr>
      </thead>
      <tfoot>
        <xsl:variable name="maxResolution">
         <xsl:for-each select="//REPORT/INDICATORS/INDICATOR">
            <xsl:sort select="WIDTH * HEIGHT" data-type="number" order="descending"/>
            <xsl:if test="position()=1">
              <xsl:value-of select="WIDTH"/> x <xsl:value-of select="HEIGHT"/>
            </xsl:if>
          </xsl:for-each>
        </xsl:variable>
        <xsl:variable name="minResolution">
         <xsl:for-each select="//REPORT/INDICATORS/INDICATOR">
            <xsl:sort select="WIDTH * HEIGHT" data-type="number" order="ascending"/>
            <xsl:if test="position()=1">
              <xsl:value-of select="WIDTH"/> x <xsl:value-of select="HEIGHT"/>
            </xsl:if>
          </xsl:for-each>
        </xsl:variable>
        <tr>
          <td colspan="3" class="manage-column"><xsl:value-of select="//REPORT/TRANSLATE/COUNT_RESOLUTION"/></td>
          <td class="manage-column" style="text-align:center;"><b><xsl:value-of select="count(//REPORT/INDICATORS/INDICATOR)"/></b></td>
        </tr>
        <tr>
          <td colspan="3" class="manage-column"><xsl:value-of select="//REPORT/TRANSLATE/MAX_RESOLUTION"/></td>
          <td class="manage-column" style="text-align:center;"><b><xsl:value-of select="$maxResolution"/></b></td>
        </tr>
        <tr>
          <td colspan="3" class="manage-column"><xsl:value-of select="//REPORT/TRANSLATE/MIN_RESOLUTION"/></td>
          <td class="manage-column" style="text-align:center;"><b><xsl:value-of select="$minResolution"/></b></td>
        </tr>
        <tr>
          <td colspan="3" class="manage-column"><xsl:value-of select="//REPORT/TRANSLATE/NORESOLUTIONDETECT"/></td>
          <td class="manage-column" style="text-align:center;"><b><xsl:value-of select="//REPORT/INDICATORS/NOTSET"/></b></td>
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
                <td class="center">
                  <div class="screen">
                    <xsl:variable name="dW" select="32 * WIDTH div HEIGHT"/>
                    <xsl:attribute name="style">width:<xsl:value-of select="$dW"/>px; height: 32px;</xsl:attribute>
                  </div>
                </td>
                <td>
                  <xsl:value-of select="WIDTH"/> x <xsl:value-of select="HEIGHT"/>
                </td>
                <td class="center">
                  <xsl:value-of select="COUNT"/>
                </td>
              </tr>
              <tr></tr>
              <tr>
                <xsl:if test="$maxUniq&gt;0">
                  <td colspan="4" style="padding: 0px;">
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