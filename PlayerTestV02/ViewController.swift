//
//  ViewController.swift
//  PlayerTestV02
//
//  Created by jinyaoMa on 2018/1/3.
//  Copyright © 2018年 jinyaoMa. All rights reserved.
//

import UIKit
import WebKit

class ViewController: UIViewController, UIScrollViewDelegate, WKScriptMessageHandler {
    
    var theWebView: WKWebView = WKWebView(frame: UIScreen.main.bounds)
    var isStatusBarColorChanged = false
    
    func userContentController(_ userContentController: WKUserContentController, didReceive message: WKScriptMessage) {
        let fileManager = FileManager.default
        let document = fileManager.urls(for: .documentDirectory, in: .userDomainMask)
        let documentUrl = document[0] as URL
        if (!fileManager.fileExists(atPath: documentUrl.path + "/playertestmusic")) {
            try! fileManager.createDirectory(atPath: documentUrl.path + "/playertestmusic", withIntermediateDirectories: true, attributes: nil)
        }
        let localMusic: [URL] = try! fileManager.contentsOfDirectory(at: documentUrl.appendingPathComponent("playertestmusic", isDirectory: true), includingPropertiesForKeys: nil, options: .skipsHiddenFiles)
        
        switch message.name {
        case "getLocalMusic":
            var localMusicLJson: String = "["
            var documentPath: String = "["
            if (localMusic.count > 0){
                for i in 0...(localMusic.count - 1) {
                    localMusicLJson += "'" + localMusic[i].lastPathComponent + "'"
                    documentPath += "'" + localMusic[i].path + "'"
                    if (i < (localMusic.count - 1)) {
                        localMusicLJson += ", "
                        documentPath += ", "
                    }
                }}
            localMusicLJson += "]"
            documentPath += "]"
            theWebView.evaluateJavaScript("getLocalMusic(\(localMusicLJson), \(documentPath))", completionHandler: { (any, error) in
                if (error != nil) {
                    print(error.debugDescription)
                }
            })
        case "toggleLocalMusic":
            if let dic = message.body as? NSDictionary {
                let filename: String = (dic["filename"] as AnyObject).description
                let ip: String = (dic["ip"] as AnyObject).description
                let download: String = "http://\(ip)/Music/\(filename.addingPercentEncoding(withAllowedCharacters: .urlQueryAllowed) ?? "")"
                let downloadUrl: URL = URL(string: download)!
                var flag = true
                if (localMusic.count > 0){
                    for i in 0...(localMusic.count - 1) {
                        if (localMusic[i].lastPathComponent == filename) {
                            if (fileManager.isDeletableFile(atPath: localMusic[i].path)){
                                try! fileManager.removeItem(atPath: localMusic[i].path)
                            }
                            flag = false
                        }
                    }
                }
                if (flag) {
                    let session = URLSession.shared
                    let downloadTask = session.downloadTask(with: downloadUrl, completionHandler: { (location, response, downloadError) in
                        if (downloadError == nil) {
                            let locationPath = location?.path
                            let htmlMusic:String = documentUrl.path + "/playertestmusic/" + filename
                            try! fileManager.moveItem(atPath: locationPath!, toPath: htmlMusic)
                            self.theWebView.evaluateJavaScript("downloadMusic(true)", completionHandler: { (any, error) in
                                if (error != nil) {
                                    print(error.debugDescription)
                                }
                            })
                        }
                    })
                    downloadTask.resume()
                } else {
                    theWebView.evaluateJavaScript("downloadMusic(false)", completionHandler: { (any, error) in
                        if (error != nil) {
                            print(error.debugDescription)
                        }
                    })
                }
            }
        case "clearLocalMusic":
            if (localMusic.count > 0){
                for i in 0...(localMusic.count - 1) {
                    if (fileManager.isDeletableFile(atPath: localMusic[i].path)){
                        try! fileManager.removeItem(atPath: localMusic[i].path)
                    }
                }
                theWebView.evaluateJavaScript("localLocalMusic()", completionHandler: { (any, error) in
                    if (error != nil) {
                        print(error.debugDescription)
                    }
                })
            }
        case "toggleStatusBar":
            isStatusBarColorChanged = !isStatusBarColorChanged
            if (isStatusBarColorChanged) {
                theWebView.evaluateJavaScript("goToPlayer()", completionHandler: { (any, error) in
                    if (error == nil) {
                        self.setNeedsStatusBarAppearanceUpdate()
                    } else {
                        print(error.debugDescription)
                    }
                })
            } else {
                theWebView.evaluateJavaScript("goBackToPages()", completionHandler: { (any, error) in
                    if (error == nil) {
                        self.setNeedsStatusBarAppearanceUpdate()
                    } else {
                        print(error.debugDescription)
                    }
                })
            }
        case "throwError":
            print(message.body)
        default: break
        }
    }
    
    override func viewDidLoad() {
        super.viewDidLoad()
        // Do any additional setup after loading the view, typically from a nib.
        let requestUrl = URL(fileURLWithPath: Bundle.main.path(forResource: "index", ofType: ".html", inDirectory: "public_html")!)
        let documentUrl = FileManager.default.urls(for: .documentDirectory, in: .userDomainMask)[0]
        
        theWebView.scrollView.bounces = false
        theWebView.configuration.preferences.minimumFontSize = 16
        theWebView.configuration.preferences.javaScriptEnabled = true
        theWebView.configuration.preferences.javaScriptCanOpenWindowsAutomatically = false
        theWebView.configuration.userContentController.add(self, name: "getLocalMusic")
        theWebView.configuration.userContentController.add(self, name: "toggleLocalMusic")
        theWebView.configuration.userContentController.add(self, name: "throwError")
        theWebView.configuration.userContentController.add(self, name: "toggleStatusBar")
        theWebView.configuration.allowsInlineMediaPlayback = true
        theWebView.configuration.allowsAirPlayForMediaPlayback = true
        theWebView.scrollView.delegate = self
        
        theWebView.loadFileURL(requestUrl, allowingReadAccessTo: requestUrl)
        theWebView.loadFileURL(requestUrl, allowingReadAccessTo: documentUrl)
        self.view.addSubview(theWebView)
        
        let swipeRight = UISwipeGestureRecognizer(target: self, action: #selector(swipeGesture))
        let swipeLeft = UISwipeGestureRecognizer(target: self, action: #selector(swipeGesture))
        swipeLeft.direction = UISwipeGestureRecognizerDirection.left
        self.view.addGestureRecognizer(swipeRight)
        self.view.addGestureRecognizer(swipeLeft)
    }
    
    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
        // Dispose of any resources that can be recreated.
    }
    
    override var preferredStatusBarStyle: UIStatusBarStyle {
        return isStatusBarColorChanged ? .lightContent : .default
    }
    
    func viewForZooming(in scrollView: UIScrollView) -> UIView? {
        return nil
    }
    
    @objc func swipeGesture(sender: UISwipeGestureRecognizer) -> Void {
        switch sender.direction {
        case UISwipeGestureRecognizerDirection.left:
            theWebView.evaluateJavaScript("nativeNavFlag = 1; onClickNavPage();", completionHandler: { (any, error) in
                if (error != nil) {
                    print(error.debugDescription)
                }
            })
        case UISwipeGestureRecognizerDirection.right:
            theWebView.evaluateJavaScript("nativeNavFlag = -1; onClickNavPage();", completionHandler: { (any, error) in
                if (error != nil) {
                    print(error.debugDescription)
                }
            })
        default:
            break
        }
    }
}

