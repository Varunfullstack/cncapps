import React from 'react';

import './ToolTip.css';
import * as PropTypes from "prop-types";
import ReactTooltip from 'react-tooltip';
import { makeid } from '../utils/utils';

class ToolTip extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.state = {};
    }

    render() {        
      const id=makeid(5);
        const { title, children, width, content, style } = this.props;
        if (!title)
          return (
            <div style={{ width: width, ...style }}>
              {children}
              {content}
            </div>
          );
        else
          return (
            <div style={{ width: width, ...style }}>
              <div data-tip data-for={id} style={{display:"inline"}}>
                {children}
                {content}
              </div>
              <ReactTooltip
                id={id}
                place="bottom"
                type="dark"
                effect="float"
              >
                {title}
              </ReactTooltip>
            </div>
          );

        // if (!title)
        //   return (
        //     <div style={{ width: width, ...style }}>
        //       {children}
        //       {content}
        //     </div>
        //   );
        // else
        //   return (
        //     <div style={{ width: width, ...style }}>
        //       <div className="tooltip">
        //         {children}
        //         {content}
        //         <div className="tooltiptext tooltip-bottom" key="tooltipText">
        //           {title}
        //         </div>
        //       </div>
        //     </div>
        //   );
    }
}

export default ToolTip;

ToolTip.propTypes = {
    title: PropTypes.string,
    width: PropTypes.any,
    style: PropTypes.object
};