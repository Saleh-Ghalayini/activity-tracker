import './style.css';
import '../../index.css'
import { React, useState } from "react";
import { useDropzone } from "react-dropzone";

const Upload = () => {

    const [file, setFile] = useState(null);

    // Storing the first file being dropped
    const onDrop = (acceptedFiles) => {
        setFile(acceptedFiles[0]);
    };

    // Handling the onDrop event while keeping the functionality of the hidden file input 
    const { getRootProps, getInputProps } = useDropzone({
        onDrop,
        accept: '.csv' // Allowing only CSV files
    }); 

    return (
        <div>
            <h1>Upload CSV file</h1>
            <div 
                className="pointer drop-box text-center" 
                {...getRootProps()}
            >
                <input {...getInputProps()} />
                <p>Drag & drop a CSV file here, or click to select one</p>
            </div>
            {file && <p>Selected file: {file.name}</p>}
        </div>
    );
};

export default Upload;