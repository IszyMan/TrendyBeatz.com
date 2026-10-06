@verbatim
<xsl:stylesheet
    version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:sm="http://www.sitemaps.org/schemas/sitemap/0.9"
    exclude-result-prefixes="sm"
>
    <xsl:output method="html" encoding="UTF-8" indent="yes"/>

    <xsl:template match="/">
        <html lang="en">
            <head>
                <title>XML Sitemap - TrendyBeatz.com</title>

                <meta
                    name="viewport"
                    content="width=device-width, initial-scale=1"
                />

                <style type="text/css">
                    body {
                        margin: 0;
                        background: #fff;
                        color: #333;
                        font-family: Arial, sans-serif;
                    }
                    
                    .sitemap-header {
                        padding: 25px;
                        background: #101713;
                        color: #ffffff;
                    }

                    .sitemap-header-inner,
                    .sitemap-content {
                        max-width: 1100px;
                        margin: 0 auto;
                    }

                    .sitemap-brand {
                        color: #16803d;
                        font-size: 24px;
                        font-weight: bold;
                        text-decoration: none;
                    }

                    .tb-sitemap {
                        max-width: 1100px;
                        margin: 40px auto;
                        padding: 0 20px;
                    }

                    .tb-sitemap p {
                        margin: 0 0 14px;
                        font-size: 14px;
                        line-height: 1.7;
                    }
                    
                    
                    .tb-sitemap h1 {
                        margin: 0 0 16px;
                        color: #16803d;
                        font-size: 28px;
                    }
                    
                    .tb-sitemap a {
                        color: #16803d;
                        text-decoration: none;
                    }
                    
                    .tb-sitemap a:hover {
                        color: #10632e;
                        text-decoration: underline;
                    }
                    
                    .tb-sitemap a:focus-visible {
                        outline: 2px solid #16803d;
                        outline-offset: 3px;
                    }
                    
                    .tb-sitemap-table th {
                        padding: 12px;
                        background: #16803d;
                        color: #fff;
                        text-align: left;
                    }
                    
                    .tb-sitemap-table tbody tr:nth-child(even) {
                        background: #f2faf5;
                    }

                    .tb-sitemap-table {
                        width: 100%;
                        margin-top: 24px;
                        border-collapse: collapse;
                        table-layout: fixed;
                        font-size: 13px;
                    }

                    .tb-sitemap-table td {
                        padding: 11px 12px;
                        border-bottom: 1px solid #e5e5e5;
                        vertical-align: top;
                    }

                    .tb-sitemap-url {
                        overflow-wrap: anywhere;
                    }

                    .tb-sitemap-date {
                        width: 230px;
                        white-space: nowrap;
                    }
                    
                    .tb-sitemap-frequency {
                        width: 130px;
                    }
                    
                    .tb-sitemap-priority {
                        width: 80px;
                    }
                    
                    @media (max-width: 600px) {
                        .tb-sitemap-frequency {
                            width: 75px;
                            overflow-wrap: anywhere;
                        }
                    
                        .tb-sitemap-priority {
                            width: 55px;
                        }
                    
                        .tb-sitemap-date {
                            width: 100px;
                        }
                    }

                    @media (max-width: 600px) {
                        .tb-sitemap {
                            margin: 24px auto;
                            padding: 0 12px;
                        }

                        .tb-sitemap h1 {
                            font-size: 23px;
                        }

                        .tb-sitemap-table {
                            font-size: 12px;
                        }

                        .tb-sitemap-table th,
                        .tb-sitemap-table td {
                            padding: 9px 7px;
                        }

                        .tb-sitemap-date {
                            width: 145px;
                            white-space: normal;
                            overflow-wrap: anywhere;
                        }
                    }
                </style>
            </head>

            <body>
                
                <header class="sitemap-header">
                    <div class="sitemap-header-inner">
                        <a href="/" class="sitemap-brand">
                            TrendyBeatz
                        </a>
                    </div>
                </header>
                <main class="tb-sitemap">
                    <h1>XML Sitemap</h1>

                    <p>
                        This XML sitemap helps search engines discover
                        published content on TrendyBeatz.com.
                    </p>

                    <xsl:choose>
                        <xsl:when test="sm:sitemapindex">
                            <p>
                                This sitemap index contains
                                <strong>
                                    <xsl:value-of
                                        select="count(sm:sitemapindex/sm:sitemap)"
                                    />
                                </strong>
                                sitemap files.
                            </p>
                        </xsl:when>

                        <xsl:otherwise>
                            <p>
                                This sitemap contains
                                <strong>
                                    <xsl:value-of
                                        select="count(sm:urlset/sm:url)"
                                    />
                                </strong>
                                URLs.
                            </p>

                            <p>
                                <a href="/sitemaps.xml">
                                    Return to sitemap index
                                </a>
                            </p>
                        </xsl:otherwise>
                    </xsl:choose>

                    <table class="tb-sitemap-table">
                        <thead>
                            <tr>
                                <th scope="col">Sitemap URL</th>
                        
                                <xsl:if test="sm:urlset">
                                    <th scope="col" class="tb-sitemap-frequency">
                                        Change Frequency
                                    </th>
                                    <th scope="col" class="tb-sitemap-priority">
                                        Priority
                                    </th>
                                </xsl:if>
                        
                                <th scope="col" class="tb-sitemap-date">
                                    Last Modified
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <xsl:for-each
                                select="sm:sitemapindex/sm:sitemap | sm:urlset/sm:url"
                            >
                                <tr>
                                    <td class="tb-sitemap-url">
                                        <a href="{sm:loc}">
                                            <xsl:value-of select="sm:loc"/>
                                        </a>
                                    </td>

                                    <xsl:if test="parent::sm:urlset">
                                        <td class="tb-sitemap-frequency">
                                            <xsl:value-of select="sm:changefreq"/>
                                        </td>
                                    
                                        <td class="tb-sitemap-priority">
                                            <xsl:value-of select="sm:priority"/>
                                        </td>
                                    </xsl:if>
                                    
                                    <td class="tb-sitemap-date">
                                        <xsl:value-of select="sm:lastmod"/>
                                    </td>
                                </tr>
                            </xsl:for-each>
                        </tbody>
                    </table>
                </main>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
@endverbatim