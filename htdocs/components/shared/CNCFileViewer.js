import Modal from "./Modal/modal";

import React, { Fragment } from "react";
import FileViewer from 'react-file-viewer';
class CNCFileViewer extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      showFile: false,
      maxHeight:100,
    };
  }

  componentDidMount() {
    // new ResizeObserver(()=>{
    //   console.log("resize")
    // }).observe()
  }
  fileType(str) {
    if(["png", "jpg"].indexOf(str) >= 0)
      return "image";
    return str;
    
  }
  handleMouseMove = () => {
    if(!this.state.showFile)
    this.setState({ showFile: true });
  };
  handleMouseLeave = () => {
    this.setState({ showFile: false });
  };
  getFileElement = () => {
    const { type, filePath, style } = this.props;
    console.log(type);
    const fType = type && type.toLocaleLowerCase();
    if (this.fileType(fType) == "image")
      return (
        <img
          src={filePath}
          style={style}
          onLoad={this.handleContainerLoad}
        ></img>
      );
    if (this.fileType(fType) == "pdf") {
      return (
        <embed
          src={filePath}
          id="displayFile"
          width={600}
          height={700}
          type="application/pdf"
        />
      );
    }

    return <div style={{width:600,height:700}}><FileViewer
    fileType={this.fileType(fType) }
    filePath={filePath}
    ></FileViewer>
    </div>;
  };
  handleContainerLoad=({target:img})=>{    
     if(img.offsetHeight>100)
    this.setState({maxHeight:img.offsetHeight})
  }
  getMaxHeight=()=>{
    console.log("getMaxHeight")
    const { type } = this.props;
    const fType = type && type.toLocaleLowerCase();
    const {maxHeight}=this.state;
    if(this.fileType(fType)=="image")
      return maxHeight;    
    else return 500;
  }
  render() {     
    const { showFile,maxHeight } = this.state;
    const {style}=this.props;
    console.log("height",this.getMaxHeight())
    return (
      <div
      
        onMouseMove={this.handleMouseMove}
        onMouseLeave={this.handleMouseLeave}
        style={{ display: "flex", flexDirection: "row" }}
      >
        <div> {this.props.children} </div>
        <div style={{backgroundColor:"green"}} >
          <div style={{ position: "absolute"}}>
            {showFile?
          <div
           
            style={{
              display:  "flex" ,
              position: "relative",
              overflow:"hidden",
              zIndex:10000,
              //backgroundColor: "#404041",
              backgroundColor: "#fff",
              maxWidth: 700,
              maxHeight: 500,
              minWidth:200,
              minHeight:100,
              justifyContent:"center",
              alignItems:"flex-start",
              padding:10,
              borderRadius:10,
              marginTop:(-1*this.getMaxHeight()),
              marginLeft:10,
              
            }}
             
          >
            {this.getFileElement()}
          </div> :null}
          </div>
        </div>
      </div>
    );
  }
}

export default CNCFileViewer;
