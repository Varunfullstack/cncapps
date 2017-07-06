SELECT
        SUM(caa_endtime - caa_starttime)
      FROM
        callactivity
      WHERE
        caa_contract_cuino = 0 
      AND
        caa_date BETWEEN '2012-03-01' AND '2012-03-31'
      AND
        caa_consno = 29